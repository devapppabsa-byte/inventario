<?php

namespace Tests\Feature;

use App\Models\CorreoEnvio;
use App\Mail\PedidoTinta;
use App\Models\EmailSetting;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Swift_TransportException;
use Tests\TestCase;

/**
 * Pedidos de tintas: correo unico y copia para quien pidio.
 *
 * Este flujo no tenia ninguna prueba. El bug mas grave era invisible por eso:
 * se mandaban dos correos con el mismo pedido (el primero sin copia y el
 * segundo con la copia), asi que recepcion recibia el pedido duplicado.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea). El disco 'local' va con
 * Storage::fake para no dejar archivos de prueba en storage/app.
 */
class PedidoTintaTest extends TestCase
{
    use DatabaseTransactions;

    private $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();

        EmailSetting::firstOrCreate(
            ['seccion' => 'tintas'],
            [
                'mail_from' => 'tintas@test.local',
                'mail_from_name' => 'Tintas',
                'destinatario' => 'recepcion@test.local',
                'cc' => 'copia@test.local',
            ]
        );

        $this->usuario = User::create([
            'name' => 'Usuario Tintas ' . uniqid(),
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

    // ---------------------------------------------------------------- helpers

    /** PNG real de las medidas exigidas por la validacion, generado con GD. */
    private function fotoValida(int $ancho = 320, int $alto = 320): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'tinta');
        $imagen = imagecreatetruecolor($ancho, $alto);
        imagefill($imagen, 0, 0, imagecolorallocate($imagen, 200, 200, 200));
        imagepng($imagen, $ruta);
        imagedestroy($imagen);

        return new UploadedFile($ruta, 'tintas.png', 'image/png', null, true);
    }

    private function pedir(): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->usuario)->post(route('tintas.pedido'), [
            'numero' => 'Otra',
            'marca' => 'HP',
            'checkboxes' => ['Cian', 'Magenta'],
            'foto_tintas' => $this->fotoValida(),
        ]);
    }

    /**
     * El mailable guarda los destinarios como lista de ['name','address'], no
     * como cadenas sueltas.
     */
    private function direcciones(array $recipientes): array
    {
        return array_map(function ($recipiente) {
            return mb_strtolower(is_array($recipiente) ? ($recipiente['address'] ?? '') : (string) $recipiente);
        }, $recipientes);
    }

    // ------------------------------------------------------------------ tests

    public function test_el_pedido_se_manda_una_sola_vez_aunque_haya_cc()
    {
        $this->pedir()->assertSessionHasNoErrors();

        // Con cc configurado, el flujo antiguo mandaba dos correos con el mismo
        // pedido: el primero sin copia y el segundo con la copia.
        $this->assertCount(1, Mail::sent(PedidoTinta::class));
    }

    public function test_quien_pide_recibe_copia_del_correo()
    {
        $this->pedir()->assertSessionHasNoErrors();

        Mail::assertSent(PedidoTinta::class, function ($correo) {
            $copias = $this->direcciones($correo->cc);

            // La copia va a users.correo, no a users.email: email es la
            // credencial de ingreso. Si alguno vuelve a leer la columna
            // equivocada, estas dos aserciones lo detectan.
            return in_array(mb_strtolower($this->usuario->correo), $copias, true)
                && !in_array(mb_strtolower($this->usuario->email), $copias, true);
        });
    }

    public function test_quien_pide_no_aparece_dos_veces_en_copia()
    {
        // El panel guarda el cc como texto; si el usuario ya estaba ahi, con
        // otra capitalizacion o entre espacios, no debe quedar repetido.
        EmailSetting::where('seccion', 'tintas')->update(['cc' => '  ' . strtoupper($this->usuario->correo) . ' , ']);

        $this->pedir()->assertSessionHasNoErrors();

        Mail::assertSent(PedidoTinta::class, function ($correo) {
            $propio = mb_strtolower($this->usuario->correo);

            return count(array_keys($this->direcciones($correo->cc), $propio, true)) === 1;
        });
    }

    public function test_el_correo_responde_a_quien_pidio_y_no_a_sistemas()
    {
        $this->pedir()->assertSessionHasNoErrors();

        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        // El From es la casilla del sistema; sin Reply-To, la respuesta de
        // recepcion se lee sola a sistemas@ y el cliente se queda sin saber que
        // paso con su pedido. El replyTo se arma dentro de build(), asi que
        // Mail::fake() no alcanza: hay que construir el mailable a mano.
        $correo = (new PedidoTinta($pedido, 'tintas@test.local', 'Tintas'))->build();

        $responders = $this->direcciones($correo->replyTo);

        $this->assertContains(mb_strtolower($this->usuario->correo), $responders);
        $this->assertNotContains(mb_strtolower($this->usuario->email), $responders);

        $nombres = array_column($correo->replyTo, 'name', 'address');
        $this->assertSame($this->usuario->name, $nombres[$this->usuario->correo]);
    }

    /**
     * Renderiza la plantilla del correo de tintas. No se usa Mailable::render()
     * porque delega en mailer->render() y el MailFake no lo trae; en su lugar
     * se pasa un doble de $message, que es lo unico que la vista usa para
     * incrustar las imagenes.
     */
    private function renderCorreoDeTintas(?Pedido $pedido = null): string
    {
        if ($pedido === null) {
            $this->pedir()->assertSessionHasNoErrors();
            $pedido = Pedido::where('user_id', $this->usuario->id)->latest('id')->first();
        }

        $foto = \Illuminate\Support\Facades\Storage::disk('local')->path($pedido->foto_tintas);
        $message = new class {
            public function embed($ruta)
            {
                return 'cid:' . md5($ruta);
            }
        };

        return view('emails.pedido_tinta', ['pedido' => $pedido, 'foto_path' => $foto, 'message' => $message])->render();
    }

    public function test_el_correo_de_tintas_solo_usa_negro_gris_y_blanco()
    {
        $html = $this->renderCorreoDeTintas();

        preg_match_all('/#([0-9a-fA-F]{6})/i', $html, $coincidencias);

        $this->assertNotEmpty($coincidencias[1], 'El correo deberia traer colores propios.');

        foreach ($coincidencias[1] as $hex) {
            $rojo = hexdec(substr($hex, 0, 2));
            $verde = hexdec(substr($hex, 2, 2));
            $azul = hexdec(substr($hex, 4, 2));

            $this->assertSame($rojo, $verde, "El color #$hex tiene el canal rojo distinto al verde.");
            $this->assertSame($verde, $azul, "El color #$hex tiene el canal verde distinto al azul.");
        }
    }

    public function test_el_correo_de_tintas_conserva_la_informacion_del_pedido()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        $html = $this->renderCorreoDeTintas($pedido);

        $this->assertStringContainsString($pedido->user->name, $html);
        $this->assertStringContainsString($pedido->user->correo, $html);
        $this->assertStringNotContainsString($pedido->user->email, $html);
        $this->assertStringContainsString('HP', $html);
        $this->assertStringContainsString('Cian', $html);
        $this->assertStringContainsString('Magenta', $html);
        $this->assertStringContainsString($pedido->fecha_pedido, $html);
    }

    public function test_el_asunto_del_correo_de_tintas_lleva_el_numero()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        $correo = (new PedidoTinta($pedido, 'tintas@test.local', 'Tintas'))->build();

        $this->assertStringContainsString('#' . $pedido->id, $correo->subject);
        $this->assertStringContainsString($pedido->numero, $correo->subject);
    }

    public function test_el_pedido_no_acepta_un_archivo_que_no_es_imagen()
    {
        Storage::fake('local');
        $antes = Pedido::count();

        $this->actingAs($this->usuario)->post(route('tintas.pedido'), [
            'numero' => 'Otra',
            'marca' => 'HP',
            'checkboxes' => ['Negro'],
            'foto_tintas' => UploadedFile::fake()->create('ficha.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('foto_tintas');

        $this->assertSame($antes, Pedido::count());

        // El mensaje de por defecto ("foto tintas debe ser una imagen") no dice
        // que el campo es una foto ni que se rechazan documentos, asi que se
        // comprueba la frase propia: si alguien la quita, el test lo nota.
        $mensajes = session('errors')->getBag('default')->getMessages();
        $this->assertArrayHasKey('foto_tintas', $mensajes);
        $this->assertStringContainsString(
            'No se aceptan documentos',
            implode(' ', $mensajes['foto_tintas'])
        );
    }

    // ------------------------------------------------------------------ SMTP

    public function test_un_timeout_de_smtp_no_convierte_el_pedido_guardado_en_500()
    {
        // Reemplazamos el mailer por uno que lanza el error exacto del incidente.
        // Solo entonces se puede simular un fallo real sin tocar la red.
        Mail::swap(Mockery::mock(MailerContract::class)->shouldReceive('to')
            ->once()
            ->andThrow(new Swift_TransportException('Connection to tcp://smtp.gmail.com:587 Timed Out'))
            ->getMock());

        $this->pedir()
            ->assertRedirect()
            ->assertSessionHas('correo_fallido')
            ->assertSessionHas('pedido_enviado');

        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();
        $this->assertNotNull($pedido, 'El pedido debe seguir guardado aunque el correo falle.');

        $envio = CorreoEnvio::ultimoPara($pedido);
        $this->assertNotNull($envio);
        $this->assertSame(CorreoEnvio::ESTADO_FALLIDO, $envio->estado);
        $this->assertSame('tintas', $envio->seccion);
        $this->assertSame(1, $envio->intentos);
        $this->assertStringContainsString('Timed Out', $envio->error);
    }

    public function test_el_reenvio_es_solo_de_quien_pidio()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        $otro = User::create([
            'name' => 'Otro usurpador ' . uniqid(),
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

        $this->actingAs($otro)
            ->post(route('tintas.reenvio', $pedido->id))
            ->assertNotFound();
    }

    public function test_el_dueno_puede_reenviar_el_aviso_de_su_pedido()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        $this->actingAs($this->usuario)
            ->post(route('tintas.reenvio', $pedido->id))
            ->assertRedirect()
            ->assertSessionHas('reenviado');

        $envio = CorreoEnvio::ultimoPara($pedido);
        $this->assertSame(CorreoEnvio::ESTADO_ENVIADO, $envio->estado);
        $this->assertSame(2, $envio->intentos, 'El reenvio debe contar como un intento mas.');
    }

    public function test_un_reenvio_fallido_no_devuelve_500_y_queda_auditado()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();

        Mail::swap(Mockery::mock(MailerContract::class)->shouldReceive('to')
            ->once()
            ->andThrow(new Swift_TransportException('Connection to tcp://smtp.gmail.com:587 Timed Out'))
            ->getMock());

        $this->actingAs($this->usuario)
            ->post(route('tintas.reenvio', $pedido->id))
            ->assertRedirect()
            ->assertSessionHas('reenvio_fallido')
            ->assertSessionHas('correo_fallido');

        $envio = CorreoEnvio::ultimoPara($pedido);
        $this->assertSame(CorreoEnvio::ESTADO_FALLIDO, $envio->estado);
        $this->assertSame(2, $envio->intentos);
    }

    public function test_el_boton_de_reenvio_solo_sale_cuando_el_ultimo_intento_fallo()
    {
        $this->pedir()->assertSessionHasNoErrors();
        $pedido = Pedido::where('user_id', $this->usuario->id)->latest()->first();
        $ruta = route('tintas.reenvio', $pedido->id);

        $pagina = $this->actingAs($this->usuario)->get(route('tintas.show'));

        $this->assertStringNotContainsString($ruta, $pagina->getContent(), 'Sin fallo previo no debe aparecer el boton.');

        CorreoEnvio::create([
            'seccion' => 'tintas',
            'enviable_type' => Pedido::class,
            'enviable_id' => $pedido->id,
            'estado' => CorreoEnvio::ESTADO_FALLIDO,
            'destinatarios' => 'recepcion@test.local',
            'intentos' => 2,
            'error' => 'Timed Out',
            'intento_en' => now(),
        ]);

        $pagina = $this->actingAs($this->usuario)->get(route('tintas.show'));

        $this->assertStringContainsString($ruta, $pagina->getContent(), 'Con el ultimo intento fallido debe aparecer el boton.');
    }
}
