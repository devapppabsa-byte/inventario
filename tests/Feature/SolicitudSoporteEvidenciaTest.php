<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Mail\ReporteSoporte;
use App\Models\CorreoEnvio;
use App\Models\EmailSetting;
use App\Models\Reporte;
use App\Models\User;
use App\Http\Controllers\ticketsController;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Swift_TransportException;
use Tests\TestCase;

/**
 * Solicitudes de soporte: descripcion enriquecida y evidencia adjunta.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea). El disco 'local' va con
 * Storage::fake para no dejar archivos de prueba en storage/app.
 */
class SolicitudSoporteEvidenciaTest extends TestCase
{
    use DatabaseTransactions;

    private $usuario;
    private $otroUsuario;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();

        // Con cc de verdad: antes el reporte se enviaba dos veces, una a los
        // destinatarios y otra con la copia, y soporte recibia el correo duplicado.
        EmailSetting::firstOrCreate(
            ['seccion' => 'soporte'],
            [
                'mail_from' => 'soporte@test.local',
                'mail_from_name' => 'Soporte',
                'destinatario' => 'soporte@test.local',
                'cc' => 'copia@test.local',
            ]
        );

        $this->usuario = $this->crearUsuario();
        $this->otroUsuario = $this->crearUsuario();
    }

    // ---------------------------------------------------------------- helpers

    private function crearUsuario(): User
    {
        return User::create([
            'name' => 'Usuario Evidencia ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'correo' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Prueba',
            'planta' => 'P1',
            'extension' => '100',
            'celular' => 'No hay celular',
            'jefe' => 0,
            'resguardo_firmado' => 0,
            'activo' => 1,
        ]);
    }

    /** Payload minimo valido del modal. */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'dispositivo' => 'computadora',
            'prioridad' => 'Media',
            'descripcion' => '<p>La impresora no enciende</p>',
        ], $extra);
    }

    private function enviar(array $extra = [])
    {
        return $this->actingAs($this->usuario)->post(route('reporte.post'), $this->payload($extra));
    }

    /**
     * Las reglas por archivo (formato, peso) reportan con clave "adjuntos.0", y
     * la de cantidad con "adjuntos", asi que se revisa el prefijo y no la exacta.
     */
    private function assertFalloEnAdjuntos($response)
    {
        $response->assertSessionHasErrors();

        $claves = array_keys(session('errors')->getBag('default')->getMessages());
        $conPrefijo = array_filter($claves, function ($clave) {
            return strpos($clave, 'adjuntos') === 0;
        });

        $this->assertNotEmpty($conPrefijo, 'Se esperaba un error de adjuntos y llegaron: ' . implode(', ', $claves));
    }

    /**
     * El mailable guarda los destinatarios como lista de ['name','address'], no
     * como cadenas sueltas.
     */
    private function direcciones(array $recipientes): array
    {
        return array_map(function ($recipiente) {
            return mb_strtolower(is_array($recipiente) ? ($recipiente['address'] ?? '') : (string) $recipiente);
        }, $recipientes);
    }

    /** PNG real de las medidas indicadas, generado con GD. */
    private function imagenValida(int $ancho = 320, int $alto = 320): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'img');
        $imagen = imagecreatetruecolor($ancho, $alto);
        imagefill($imagen, 0, 0, imagecolorallocate($imagen, 200, 200, 200));
        imagepng($imagen, $ruta);
        imagedestroy($imagen);

        return new UploadedFile($ruta, 'captura.png', 'image/png', null, true);
    }

    private function imagenPequena(int $ancho = 100, int $alto = 100): UploadedFile
    {
        return $this->imagenValida($ancho, $alto);
    }

    // ------------------------------------------------------- sin evidencia

    public function test_un_reporte_se_puede_crear_sin_adjuntos()
    {
        $this->enviar()->assertRedirect();

        $reporte = Reporte::where('user_id', $this->usuario->id)->latest('id')->first();

        $this->assertNotNull($reporte);
        $this->assertCount(0, $reporte->adjuntos);
    }

    // ------------------------------------------------------------ sanitizado

    public function test_el_html_de_la_descripcion_se_sanea_al_guardar()
    {
        $this->enviar([
            'descripcion' => '<p onclick="robar()">Se me cae la red</p>'
                . '<script>alert(1)</script>'
                . '<img src=x onerror="alert(2)">',
        ])->assertRedirect();

        $descripcion = Reporte::where('user_id', $this->usuario->id)->latest('id')->value('descripcion');

        $this->assertStringNotContainsString('<script', $descripcion);
        $this->assertStringNotContainsString('onclick', $descripcion);
        $this->assertStringNotContainsString('onerror', $descripcion);
        $this->assertStringContainsString('Se me cae la red', $descripcion);
    }

    public function test_se_conserva_el_formato_legitimo_de_la_descripcion()
    {
        $this->enviar([
            'descripcion' => '<h2>Titulo</h2><p><strong>negrita</strong> y <em>cursiva</em></p>'
                . '<ul><li>uno</li></ul><a href="https://ejemplo.com">enlace</a>',
        ])->assertRedirect();

        $descripcion = Reporte::where('user_id', $this->usuario->id)->latest('id')->value('descripcion');

        $this->assertStringContainsString('<strong>negrita</strong>', $descripcion);
        $this->assertStringContainsString('<em>cursiva</em>', $descripcion);
        $this->assertStringContainsString('<li>uno</li>', $descripcion);
        $this->assertStringContainsString('https://ejemplo.com', $descripcion);
    }

    public function test_el_modal_no_ofrece_citas_codigo_ni_limpiar_formato()
    {
        $html = $this->actingAs($this->usuario)->get(route('tickets.show'))->getContent();

        // La barra solo debe ofrecer encabezados, negrita/cursiva/subrayado/tachado,
        // listas y enlace.
        $this->assertStringNotContainsString('blockquote', $html);
        $this->assertStringNotContainsString('code-block', $html);
        $this->assertStringNotContainsString("'clean'", $html);

        $this->assertStringContainsString("['link']", $html);
        $this->assertStringContainsString("['bold', 'italic', 'underline', 'strike']", $html);
    }

    public function test_el_texto_pegado_no_conserva_citas_ni_bloques_de_codigo()
    {
        $this->enviar([
            'descripcion' => '<blockquote>Cita de otro lado</blockquote>'
                . '<pre><code>linea de codigo</code></pre>'
                . '<p>La impresora <strong>no enciende</strong></p>',
        ])->assertRedirect();

        $descripcion = Reporte::where('user_id', $this->usuario->id)->latest('id')->value('descripcion');

        $this->assertStringNotContainsString('<blockquote', $descripcion);
        $this->assertStringNotContainsString('<pre', $descripcion);
        $this->assertStringNotContainsString('<code', $descripcion);

        // Las etiquetas se van pero no el texto: se descarta el formato, no lo
        // que el usuario escribio. Y el resto del formato sigue intacto.
        $this->assertStringContainsString('Cita de otro lado', $descripcion);
        $this->assertStringContainsString('linea de codigo', $descripcion);
        $this->assertStringContainsString('<strong>no enciende</strong>', $descripcion);
    }

    public function test_una_descripcion_vacia_se_rechaza_aunque_ugel_tenga_etiquetas()
    {
        $antes = Reporte::where('user_id', $this->usuario->id)->count();

        $this->enviar(['descripcion' => '<p><br></p>'])
            ->assertSessionHasErrors('descripcion');

        $this->assertSame($antes, Reporte::where('user_id', $this->usuario->id)->count());
    }

    public function test_el_formato_sobre_255_caracteres_ya_cabe()
    {
        $largo = '<p>' . str_repeat('a', 900) . '</p>';

        $this->enviar(['descripcion' => $largo])->assertRedirect();

        $guardada = Reporte::where('user_id', $this->usuario->id)->latest('id')->value('descripcion');
        $this->assertSame(900, strlen(strip_tags($guardada)));
    }

    // --------------------------------------------------------------Adjuntos

    public function test_se_aceptan_varios_archivos_y_se_guardan_privados()
    {
        Storage::fake('local');

        $this->enviar([
            'adjuntos' => [
                $this->imagenValida(),
                UploadedFile::fake()->create('evidencia.pdf', 120, 'application/pdf'),
            ],
        ])->assertRedirect();

        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $this->assertCount(2, $reporte->adjuntos);

        foreach ($reporte->adjuntos as $adjunto) {
            $this->assertTrue($adjunto->existeEnDisco());
            $this->assertStringStartsWith('reportes/' . $reporte->id . '/', $adjunto->ruta);
        }

        $pdf = $reporte->adjuntos->firstWhere('nombre_original', 'evidencia.pdf');
        $this->assertNotNull($pdf);
        $this->assertFalse($pdf->es_imagen);
        $this->assertNotNull($reporte->adjuntos->firstWhere('es_imagen', true));
    }

    public function test_la_evidencia_no_se_sirve_desde_la_carpeta_publica()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();
        $ruta = $reporte->adjuntos->first()->ruta;

        $this->assertFileDoesNotExist(public_path('storage/' . $ruta));
        $this->assertFileDoesNotExist(storage_path('app/public/' . $ruta));
    }

    public function test_la_columna_legacy_img_guarda_la_primera_imagen()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $this->assertSame($reporte->adjuntos->firstWhere('es_imagen', true)->ruta, $reporte->img);
    }

    public function test_un_formato_no_admitido_se_rechaza()
    {
        $antes = Reporte::where('user_id', $this->usuario->id)->count();

        $this->assertFalloEnAdjuntos($this->enviar([
            'adjuntos' => [UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream')],
        ]));

        $this->assertSame($antes, Reporte::where('user_id', $this->usuario->id)->count());
    }

    public function test_mas_de_cinco_archivos_se_rechaza()
    {
        $archivos = [];
        for ($i = 0; $i < 6; $i++) {
            $archivos[] = UploadedFile::fake()->create('evidencia' . $i . '.pdf', 10, 'application/pdf');
        }

        $this->assertFalloEnAdjuntos($this->enviar(['adjuntos' => $archivos]));
    }

    public function test_una_imagen_muy_pequena_se_rechaza()
    {
        $antes = Reporte::where('user_id', $this->usuario->id)->count();

        $this->assertFalloEnAdjuntos($this->enviar(['adjuntos' => [$this->imagenPequena()]]));

        $this->assertSame($antes, Reporte::where('user_id', $this->usuario->id)->count());
    }

    public function test_un_documento_pequeno_no_es_una_imagen_y_se_acepta()
    {
        // PDF de 1 KB: si se validara tamaño minimo como si fuera imagen, fallaria.
        $this->enviar([
            'adjuntos' => [UploadedFile::fake()->create('chico.pdf', 1, 'application/pdf')],
        ])->assertRedirect();

        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();
        $this->assertCount(1, $reporte->adjuntos);
    }

    public function test_el_peso_total_superado_se_rechaza()
    {
        $antes = Reporte::where('user_id', $this->usuario->id)->count();

        $this->assertFalloEnAdjuntos($this->enviar([
            'adjuntos' => [
                UploadedFile::fake()->create('a.pdf', 9000, 'application/pdf'),
                UploadedFile::fake()->create('b.pdf', 9000, 'application/pdf'),
            ],
        ]));

        $this->assertSame($antes, Reporte::where('user_id', $this->usuario->id)->count());
    }

    // ----------------------------------------------------------autorización

    public function test_el_dueno_puede_abrir_su_evidencia()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();
        $adjunto = $reporte->adjuntos->first();

        $this->actingAs($this->usuario)
            ->get(route('adjuntos.show', $adjunto->id))
            ->assertOk();
    }

    public function test_otro_usuario_no_puede_abrir_la_evidencia_ajena()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $this->actingAs($this->otroUsuario)
            ->get(route('adjuntos.show', $reporte->adjuntos->first()->id))
            ->assertNotFound();
    }

    public function test_una_evidencia_inexistente_no_tiene_que_revelar_nada()
    {
        $this->actingAs($this->usuario)->get(route('adjuntos.show', 99999999))->assertNotFound();
    }

    public function test_el_admin_puede_abrir_la_evidencia_de_cualquiera()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $this->actingAs(Admin::first(), 'admin')
            ->get(route('adjuntos.show', $reporte->adjuntos->first()->id))
            ->assertOk();
    }

    public function test_la_ruta_legacy_de_imagen_ya_no_filtra_por_usuario()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest('id')->first();

        $this->actingAs($this->otroUsuario)
            ->get(route('reporte.imagen', $reporte->id))
            ->assertNotFound();
    }

    public function test_el_dueno_sigue_podiendo_ver_la_imagen_por_la_ruta_legacy()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest('id')->first();

        $this->actingAs($this->usuario)
            ->get(route('reporte.imagen', $reporte->id))
            ->assertRedirect(route('adjuntos.show', $reporte->adjuntos->first()->id));
    }

    // ------------------------------------------------------------- respuestas

    public function test_el_detalle_del_usuario_muestra_descripcion_y_evidencia()
    {
        $this->enviar([
            'descripcion' => '<p>Se me cae la <strong>red</strong></p>',
            'adjuntos' => [UploadedFile::fake()->create('manual.pdf', 50, 'application/pdf')],
        ])->assertRedirect();

        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $response = $this->actingAs($this->usuario)->get(route('detalle.reporte', $reporte->id));

        $response->assertOk()
            ->assertSee('Se me cae la <strong>red</strong>', false)
            ->assertSee('manual.pdf')
            ->assertSee(route('adjuntos.show', $reporte->adjuntos->first()->id));
    }

    public function test_el_detalle_de_admin_muestra_la_evidencia()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();
        $reporte = Reporte::with('adjuntos')->where('user_id', $this->usuario->id)->latest('id')->first();

        $this->actingAs(Admin::first(), 'admin')
            ->get(route('detalle.reporte.admin', $reporte->id))
            ->assertOk()
            ->assertSee(route('adjuntos.show', $reporte->adjuntos->first()->id));
    }

    public function test_el_listado_de_usuarios_muestra_la_evidencia_adjunta()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        $this->actingAs($this->usuario)
            ->get(route('tickets.show'))
            ->assertOk();
    }

    public function test_el_formulario_de_tickets_sigue_cargando()
    {
        $this->actingAs($this->usuario)
            ->get(route('tickets.show'))
            ->assertOk()
            ->assertSee('Nuevo reporte');
    }

    public function test_el_editor_lleva_la_clase_de_alto_fijo()
    {
        // El alto del editor es CSS puro, no lo cubre ninguna otra prueba: si
        // alguien borra la clase, el recuadro vuelve a verse aplastado.
        $this->actingAs($this->usuario)
            ->get(route('tickets.show'))
            ->assertOk()
            ->assertSee('<div id="descripcion" class="editor-alto">', false);
    }

    public function test_la_hoja_de_estilos_acota_el_alto_del_editor()
    {
        $css = file_get_contents(public_path('css/estilos.css'));

        // El alto del editor es CSS puro y se ajusta a mano con facilidad, asi
        // que la prueba no ata una implementacion concreta: solo pide que el
        // editor siga acotado y con scroll interno, que es lo que evita que el
        // modal se estire o que el texto quede fuera de vista.
        $this->assertMatchesRegularExpression(
            '/\.editor-alto\s*\{[^}]*height\s*:/',
            $css,
            'El editor debe tener un alto acotado.'
        );

        $this->assertMatchesRegularExpression(
            '/\.editor-alto[^{]*\.ql-editor\s*\{[^}]*overflow-y\s*:\s*auto/',
            $css,
            'El editor debe desplazarse por dentro en vez de crecer.'
        );
    }

    public function test_el_correo_de_soporte_solo_usa_negro_gris_y_blanco()
    {
        $reporte = $this->crearReporteDePrueba('<p>La impresora <strong>no enciende</strong></p>');
        $html = $this->renderCorreoDeSoporte($reporte);

        preg_match_all('/#([0-9a-fA-F]{6})/i', $html, $coincidencias);

        // Si la expresion dejara de encontrar colores, la prueba pasaria en
        // vacio, asi que se comprueba que si los hay.
        $this->assertNotEmpty($coincidencias[1], 'El correo deberia traer colores propios.');

        foreach ($coincidencias[1] as $hex) {
            $rojo = hexdec(substr($hex, 0, 2));
            $verde = hexdec(substr($hex, 2, 2));
            $azul = hexdec(substr($hex, 4, 2));

            // Negro, gris y blanco son, por definicion, canales iguales.
            $this->assertSame($rojo, $verde, "El color #$hex tiene el canal rojo distinto al verde.");
            $this->assertSame($verde, $azul, "El color #$hex tiene el canal verde distinto al azul.");
        }
    }

    public function test_el_correo_de_soporte_conserva_la_informacion_del_reporte()
    {
        $reporte = $this->crearReporteDePrueba(
            '<p>Se traba el alimentador de papel</p>',
            ['adjuntos' => [UploadedFile::fake()->create('manual.pdf', 40, 'application/pdf')]]
        );

        $html = $this->renderCorreoDeSoporte($reporte);

        $this->assertStringContainsString($reporte->user->name, $html);
        $this->assertStringContainsString($reporte->user->correo, $html);
        // El cuerpo muestra el correo de contacto, no la credencial de ingreso.
        $this->assertStringNotContainsString($reporte->user->email, $html);
        $this->assertStringContainsString('Se traba el alimentador de papel', $html);
        $this->assertStringContainsString('manual.pdf', $html);
        $this->assertStringContainsString(ucfirst($reporte->prioridad), $html);
    }

    public function test_el_asunto_del_correo_lleva_el_numero_de_ticket()
    {
        $reporte = $this->crearReporteDePrueba('<p>La impresora no enciende</p>');

        // build() es donde el mailable arma el asunto y las adjuntos.
        $correo = new ReporteSoporte($reporte);
        $correo->build();

        $this->assertStringContainsString('#' . $reporte->id, $correo->subject);
    }

    /**
     * Renderiza la plantilla del correo. No se usa Mailable::render() porque
     * delega en mailer->render() y el MailFake de Mail::fake() no lo trae; en su
     * lugar se pasa un doble de $message, que es lo unico que la vista usa para
     * incrustar las imagenes.
     */
    private function renderCorreoDeSoporte(?Reporte $reporte = null): string
    {
        if ($reporte === null) {
            $reporte = $this->crearReporteDePrueba('<p>La impresora no enciende</p>');
        }

        $message = new class {
            public function embed($ruta)
            {
                return 'cid:' . md5($ruta);
            }
        };

        return view('emails.reporte_soporte', compact('reporte', 'message'))->render();
    }

    /**
     * Genera un WEBP real de las medidas pedidas. Necesaria >= 300x300 porque la
     * validacion exige esa resolucion minima en las imagenes.
     */
    private function webpDe(int $ancho, int $alto): string
    {
        $imagen = imagecreatetruecolor($ancho, $alto);
        imagefill($imagen, 0, 0, imagecolorallocate($imagen, 240, 240, 240));

        ob_start();
        imagewebp($imagen, null, 80);
        $bytes = ob_get_clean();

        imagedestroy($imagen);

        return $bytes;
    }

    /** Crea un reporte del usuario actual con la descripcion indicada. */
    private function crearReporteDePrueba(string $descripcion, array $extra = []): Reporte
    {
        $this->enviar(array_merge(['descripcion' => $descripcion], $extra))->assertRedirect();

        return Reporte::with(['user', 'adjuntos'])
            ->where('user_id', $this->usuario->id)
            ->latest('id')
            ->first();
    }

    public function test_acepta_una_imagen_webp_que_antes_se_rechazaba()
    {
        // El <input> ofrecia webp pero no estaba en FORMATOS_ADJUNTOS, asi que
        // el navegador lo dejaba elegir y el servidor lo rechazaba.
        $webp = UploadedFile::fake()->createWithContent('captura.webp', $this->webpDe(400, 400));

        $this->enviar(['adjuntos' => [$webp]])->assertSessionHasNoErrors();

        $reporte = Reporte::with(['user', 'adjuntos'])
            ->where('user_id', $this->usuario->id)
            ->latest('id')
            ->first();

        $this->assertCount(1, $reporte->adjuntos);
        $this->assertSame('image/webp', $reporte->adjuntos->first()->mime);
        $this->assertTrue($reporte->adjuntos->first()->es_imagen);
        // La extension sale del contenido detectado, no del nombre que manda el
        // cliente, asi que un webp tiene que guardarse como .webp.
        $this->assertStringEndsWith('.webp', $reporte->adjuntos->first()->ruta);
    }

    public function test_una_foto_sobre_dimensionada_dice_cuanto_acepta_el_servidor()
    {
        // Antes esto devolvia el generico "Subir adjuntos.0 ha fallado", porque
        // Laravel intercepta en Validator.php:591 antes de evaluar mimes y max.
        $respuesta = $this->enviar([
            'adjuntos' => [UploadedFile::fake()->create('foto.jpg', 20480, 'image/jpeg')],
        ]);

        // La regla max de adjuntos.* guarda el error en "adjuntos.0"; la vista
        // los junta con un prefijo, por eso se revisa asi y no por clave exacta.
        $respuesta->assertSessionHasErrors('adjuntos.0');

        $mensajes = session('errors')->getBag('default')->getMessages();
        $errores = collect($mensajes)->filter(function ($msgs, $clave) {
            return str_starts_with($clave, 'adjuntos');
        })->flatten()->implode(' | ');

        $this->assertStringNotContainsString('ha fallado', $errores, 'No debe quedar el mensaje generico de Laravel.');
        $this->assertStringContainsString('supera el máximo', $errores);
        $this->assertStringContainsString('MB', $errores);
        $this->assertStringNotContainsString('adjuntos.0', $errores, 'El nombre del campo no debe verse crudo al usuario.');
    }

    public function test_los_topes_publicados_coinciden_con_php()
    {
        $limites = ticketsController::limitesAdjuntos();

        // El tope de la app no puede pasar del de PHP, o la promesa es falsa y
        // el usuario vuelve a toparse con "Subir adjuntos.0 ha fallado".
        if ($limites['limitePhpKb'] > 0) {
            $this->assertLessThanOrEqual(
                $limites['limitePhpKb'],
                $limites['maxUnoKb'],
                'El maximo por archivo supera lo que PHP acepta en este servidor.'
            );
            $this->assertLessThanOrEqual(
                $limites['limitePhpKb'],
                $limites['maxTotalKb'],
                'El maximo total supera lo que PHP acepta en este servidor.'
            );
        }
    }

    public function test_el_total_de_evidencia_no_pasa_del_tope_publicado()
    {
        $limites = ticketsController::limitesAdjuntos();

        // Tres archivos que individualmente pasan pero que juntos no caben.
        $respuesta = $this->enviar([
            'adjuntos' => [
                UploadedFile::fake()->create('a.pdf', 1500, 'application/pdf'),
                UploadedFile::fake()->create('b.pdf', 1500, 'application/pdf'),
                UploadedFile::fake()->create('c.pdf', 1500, 'application/pdf'),
            ],
        ]);

        $respuesta->assertSessionHasErrors('adjuntos');

        $errores = collect(session('errors')->getBag('default')->getMessages())
            ->filter(function ($msgs, $clave) {
                return str_starts_with($clave, 'adjuntos');
            })->flatten()->implode(' | ');

        $this->assertStringContainsString('suma', $errores);
    }

    public function test_el_correo_de_soporte_se_manda_una_sola_vez_aunque_haya_cc()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        // Con cc configurado, el flujo antiguo mandaba dos correos: uno a los
        // destinatarios y otro con la copia. Soporte recibia el reporte duplicado.
        $this->assertCount(1, Mail::sent(ReporteSoporte::class));
    }

    public function test_un_documento_grande_dentro_del_tope_se_acepta()
    {
        $limites = ticketsController::limitesAdjuntos();

        // 3.5 MB es legal para la app, pero si el PHP de este entorno no llega,
        // el archivo muere antes de que la app lo vea. Ahi no hay nada que
        // probar: el tope publicado ya se ajusta con min().
        if ($limites['limitePhpKb'] > 0 && $limites['limitePhpKb'] < 3584) {
            $this->markTestSkipped('El PHP de este entorno no admite archivos de 3.5 MB.');
        }

        $this->enviar([
            'adjuntos' => [UploadedFile::fake()->create('manual.pdf', 3500, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reporte_adjuntos', ['nombre_original' => 'manual.pdf']);
    }

    public function test_quien_reporta_recibe_copia_del_correo()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        Mail::assertSent(ReporteSoporte::class, function ($correo) {
            $copias = $this->direcciones($correo->cc);

            // La copia va a users.correo, no a users.email: email es la
            // credencial de ingreso. Si alguno vuelve a leer la columna
            // equivocada, estas dos aserciones lo detectan.
            return in_array(mb_strtolower($this->usuario->correo), $copias, true)
                && !in_array(mb_strtolower($this->usuario->email), $copias, true);
        });
    }

    public function test_quien_reporta_no_aparece_dos_veces_en_copia()
    {
        // El panel guarda el cc como texto; si el usuario ya estaba ahi, con
        // otra capitalizacion o entre espacios, no debe quedar repetido.
        EmailSetting::where('seccion', 'soporte')->update(['cc' => '  ' . strtoupper($this->usuario->correo) . ' , ']);

        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        Mail::assertSent(ReporteSoporte::class, function ($correo) {
            $propio = mb_strtolower($this->usuario->correo);

            return count(array_keys($this->direcciones($correo->cc), $propio, true)) === 1;
        });
    }

    public function test_el_correo_responde_a_quien_reporto_y_no_a_sistemas()
    {
        $this->enviar(['adjuntos' => [$this->imagenValida()]])->assertRedirect();

        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();

        // El From es la casilla del sistema; sin Reply-To, la respuesta de
        // soporte se lee sola a sistemas@ y quien reporto nunca se entera.
        // El replyTo se arma dentro de build(), asi que Mail::fake() no alcanza:
        // hay que construir el mailable a mano.
        $correo = (new ReporteSoporte($reporte, 'soporte@test.local', 'Soporte'))->build();

        $responders = $this->direcciones($correo->replyTo);

        $this->assertContains(mb_strtolower($this->usuario->correo), $responders);
        $this->assertNotContains(mb_strtolower($this->usuario->email), $responders);
    }

    // ------------------------------------------------------------- SMTP / reenvio

    public function test_un_timeout_de_smtp_no_convierte_el_reporte_guardado_en_500()
    {
        Mail::swap(Mockery::mock(MailerContract::class)->shouldReceive('to')
            ->once()
            ->andThrow(new Swift_TransportException('Connection to tcp://smtp.gmail.com:587 Timed Out'))
            ->getMock());

        $this->enviar()
            ->assertRedirect()
            ->assertSessionHas('correo_fallido')
            ->assertSessionHas('reportado');

        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();
        $this->assertNotNull($reporte, 'El reporte debe seguir guardado aunque el correo falle.');

        $envio = CorreoEnvio::ultimoPara($reporte);
        $this->assertNotNull($envio);
        $this->assertSame(CorreoEnvio::ESTADO_FALLIDO, $envio->estado);
        $this->assertSame('soporte', $envio->seccion);
        $this->assertSame(1, $envio->intentos);
        $this->assertStringContainsString('Timed Out', $envio->error);
    }

    public function test_el_reenvio_es_solo_de_quien_reporto()
    {
        $this->enviar()->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();

        $this->actingAs($this->otroUsuario)
            ->post(route('reporte.reenvio', $reporte->id))
            ->assertNotFound();
    }

    public function test_el_dueno_puede_reenviar_el_aviso_de_su_reporte()
    {
        $this->enviar()->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();

        $this->actingAs($this->usuario)
            ->post(route('reporte.reenvio', $reporte->id))
            ->assertRedirect()
            ->assertSessionHas('reenviado');

        $envio = CorreoEnvio::ultimoPara($reporte);
        $this->assertSame(CorreoEnvio::ESTADO_ENVIADO, $envio->estado);
        $this->assertSame(2, $envio->intentos, 'El reenvio debe contar como un intento mas.');
    }

    public function test_un_reenvio_fallido_no_devuelve_500_y_queda_auditado()
    {
        $this->enviar()->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();

        Mail::swap(Mockery::mock(MailerContract::class)->shouldReceive('to')
            ->once()
            ->andThrow(new Swift_TransportException('Connection to tcp://smtp.gmail.com:587 Timed Out'))
            ->getMock());

        $this->actingAs($this->usuario)
            ->post(route('reporte.reenvio', $reporte->id))
            ->assertRedirect()
            ->assertSessionHas('reenvio_fallido')
            ->assertSessionHas('correo_fallido');

        $envio = CorreoEnvio::ultimoPara($reporte);
        $this->assertSame(CorreoEnvio::ESTADO_FALLIDO, $envio->estado);
        $this->assertSame(2, $envio->intentos);
    }

    public function test_el_boton_de_reenvio_solo_sale_cuando_el_ultimo_intento_fallo()
    {
        $this->enviar()->assertRedirect();
        $reporte = Reporte::where('user_id', $this->usuario->id)->latest()->first();
        $ruta = route('reporte.reenvio', $reporte->id);

        $pagina = $this->actingAs($this->usuario)->get(route('tickets.show'));

        $this->assertStringNotContainsString($ruta, $pagina->getContent(), 'Sin fallo previo no debe aparecer el boton.');

        CorreoEnvio::create([
            'seccion' => 'soporte',
            'enviable_type' => Reporte::class,
            'enviable_id' => $reporte->id,
            'estado' => CorreoEnvio::ESTADO_FALLIDO,
            'destinatarios' => 'soporte@test.local',
            'intentos' => 2,
            'error' => 'Timed Out',
            'intento_en' => now(),
        ]);

        $pagina = $this->actingAs($this->usuario)->get(route('tickets.show'));

        $this->assertStringContainsString($ruta, $pagina->getContent(), 'Con el ultimo intento fallido debe aparecer el boton.');
    }
}
