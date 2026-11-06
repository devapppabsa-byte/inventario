<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AgenteSoporte;
use App\Models\Reporte;
use App\Models\Seguimiento;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AgenteSoporteSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Rol Agente de Soporte: tabla y guard propios, y acceso solo a las rutas de
 * soporte gracias a la lista blanca global.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea).
 */
class SoporteAgenteRolTest extends TestCase
{
    use DatabaseTransactions;

    private $agente;
    private $usuario;
    private $reporte;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agente = AgenteSoporte::create([
            'nombre' => 'Agente Prueba ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'password' => bcrypt('agente12345'),
            'activo' => true,
        ]);

        $this->usuario = User::create([
            'name' => 'Usuario Reporta ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'correo' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Prueba Soporte',
            'planta' => 'Planta Norte',
            'extension' => '4321',
            'celular' => 'No hay celular',
            'jefe' => 0,
            'resguardo_firmado' => 0,
            'activo' => 1,
        ]);

        $this->reporte = $this->crearReporte('falla de prueba del agente', 'pendiente');
    }

    public function test_el_agente_entra_con_su_propio_formulario_y_no_ve_el_panel_admin()
    {
        $this->get('/')->assertStatus(200)->assertSee('Agente de Soporte');

        $this->post(route('ingreso.admin'), [
            'email' => $this->agente->email,
            'password' => 'agente12345',
            'tipo' => 'soporte',
        ])->assertRedirect(route('soporte.index'));

        $this->assertTrue(auth('soporte')->check());
        $this->assertFalse(auth('admin')->check());

        $this->get(route('admin.soporte'))->assertRedirect();
        $this->get(route('perfil.admin'))->assertRedirect();
    }

    public function test_las_credenciales_no_son_intercambiables_entre_agente_admin_y_usuario()
    {
        // La clave del agente no sirve para el panel de admin.
        $this->post(route('ingreso.admin'), [
            'email' => $this->agente->email,
            'password' => 'agente12345',
            'tipo' => 'admin',
        ]);
        $this->assertFalse(auth('admin')->check());

        // La clave del usuario no sirve para el rol de soporte.
        $this->post(route('ingreso.admin'), [
            'email' => $this->usuario->email,
            'password' => 'secret1234',
            'tipo' => 'soporte',
        ]);
        $this->assertFalse(auth('soporte')->check());

        // Y un admin real no puede entrar como agente.
        $admin = Admin::first();
        $this->post(route('ingreso.admin'), [
            'email' => $admin->email,
            'password' => 'admin',
            'tipo' => 'soporte',
        ]);
        $this->assertFalse(auth('soporte')->check());
    }

    public function test_una_cuenta_inactiva_no_puede_entrar()
    {
        $this->agente->update(['activo' => false]);

        $this->post(route('ingreso.admin'), [
            'email' => $this->agente->email,
            'password' => 'agente12345',
            'tipo' => 'soporte',
        ])->assertRedirect();

        $this->assertFalse(auth('soporte')->check());
    }

    public function test_las_rutas_de_soporte_exigen_sesion_de_agente()
    {
        $this->get(route('soporte.index'))->assertRedirect(route('login'));
        $this->get(route('soporte.detalle', $this->reporte->id))->assertRedirect(route('login'));
        $this->get(route('soporte.exportar'))->assertRedirect(route('login'));

        // Un usuario normal tampoco alcanza el area de soporte.
        $this->actingAs($this->usuario)->get(route('soporte.index'))->assertRedirect();
    }

    public function test_el_agente_ve_el_listado_con_su_menu_y_no_el_de_admin()
    {
        $response = $this->actingAs($this->agente, 'soporte')->get(route('soporte.index'));

        $response->assertStatus(200);
        $response->assertSee('falla de prueba del agente');
        $response->assertSee($this->agente->nombre);
        $response->assertSee(route('soporte.cerrar_sesion'));
        $response->assertDontSee(route('admin.soporte'));
    }

    public function test_el_agente_ve_el_detalle_con_los_datos_de_contacto_del_usuario()
    {
        $response = $this->actingAs($this->agente, 'soporte')
            ->get(route('soporte.detalle', $this->reporte->id));

        $response->assertStatus(200);
        $response->assertSee('falla de prueba del agente');
        $response->assertSee('Planta Norte');
        $response->assertSee('4321');
        $response->assertSee($this->usuario->correo);
        $response->assertSee(route('soporte.comentario', $this->reporte->id));
    }

    public function test_el_agente_puede_responder_pero_no_cierra_el_ticket()
    {
        $this->actingAs($this->agente, 'soporte')
            ->post(route('soporte.comentario', $this->reporte->id), [
                'comentario' => 'Ya estamos revisando tu equipo.',
            ])
            ->assertRedirect();

        $seguimiento = Seguimiento::where('reporte_id', $this->reporte->id)->latest('id')->first();
        $this->assertNotNull($seguimiento);
        $this->assertSame('Ya estamos revisando tu equipo.', $seguimiento->comentario);
        $this->assertSame($this->agente->nombre, $seguimiento->usuario);

        // El agente responde, pero el ticket sigue abierto: no resuelve nada.
        $this->reporte->refresh();
        $this->assertSame('pendiente', $this->reporte->status);
        $this->assertNull($this->reporte->calificacion);
    }

    public function test_el_agente_no_puede_responder_un_ticket_inexistente()
    {
        $this->actingAs($this->agente, 'soporte')
            ->post(route('soporte.comentario', 999999), ['comentario' => 'hola'])
            ->assertNotFound();

        $this->assertSame(0, Seguimiento::where('reporte_id', 999999)->count());
    }

    public function test_la_respuesta_exige_comentario()
    {
        $this->actingAs($this->agente, 'soporte')
            ->from(route('soporte.detalle', $this->reporte->id))
            ->post(route('soporte.comentario', $this->reporte->id), ['comentario' => ''])
            ->assertRedirect(route('soporte.detalle', $this->reporte->id))
            ->assertSessionHasErrors('comentario');

        $this->assertSame(0, Seguimiento::where('reporte_id', $this->reporte->id)->count());
    }

    public function test_la_fecha_del_seguimiento_conserva_el_minuto()
    {
        $this->actingAs($this->agente, 'soporte')
            ->post(route('soporte.comentario', $this->reporte->id), ['comentario' => 'primera']);

        $seguimiento = Seguimiento::where('reporte_id', $this->reporte->id)->latest('id')->first();

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/',
            $seguimiento->fecha
        );
    }

    public function test_el_agente_puede_exportar_el_pdf_respetando_los_filtros()
    {
        // Otro usuario, para comprobar que el filtro de la URL acota el PDF.
        $otroUsuario = $this->crearUsuario('Otro Usuario');
        $this->crearReporte('falla que no debe salir', 'pendiente', $otroUsuario->id);

        $response = $this->actingAs($this->agente, 'soporte')
            ->get(route('soporte.exportar', ['usuario' => $this->usuario->id]));

        $response->assertStatus(200);

        $pdf = $response->getContent();
        $this->assertStringContainsString('%PDF', $pdf);
        $this->assertTrue($this->pdfContiene($pdf, 'falla de prueba del agente'));
        $this->assertFalse($this->pdfContiene($pdf, 'falla que no debe salir'));
    }

    /**
     * dompdf escribe el texto en UTF-16BE y comprime los flujos, asi que se
     * inflan y se busca la cadena en esa codificacion.
     */
    private function pdfContiene($pdf, $texto)
    {
        $flujos = '';
        if (preg_match_all('/stream(.*?)endstream/s', $pdf, $coincidencias)) {
            foreach ($coincidencias[1] as $flujo) {
                $datos = trim($flujo);
                $inflado = @gzuncompress($datos);
                $flujos .= is_string($inflado) ? $inflado : $datos;
            }
        }

        foreach ([$texto, iconv('UTF-8', 'UTF-16BE', $texto)] as $variante) {
            if (is_string($variante) && $variante !== '' && strpos($flujos, $variante) !== false) {
                return true;
            }
        }

        return false;
    }

    public function test_la_lista_blanca_corta_una_sesion_de_agente_que_sale_del_area()
    {
        $this->actingAs($this->agente, 'soporte')->get(route('soporte.index'))->assertStatus(200);

        // Ruta de admin: la lista blanca cierra la sesion y manda al login.
        $this->get(route('admin.soporte'))->assertRedirect(route('login'));
        $this->assertFalse(auth('soporte')->check());

        // Y al volver a entrar ya no puede seguir.
        $this->get(route('soporte.index'))->assertRedirect(route('login'));
    }

    public function test_el_agente_puede_cerrar_su_sesion()
    {
        $this->actingAs($this->agente, 'soporte')
            ->post(route('soporte.cerrar_sesion'))
            ->assertRedirect(route('login'));

        $this->assertFalse(auth('soporte')->check());
    }

    public function test_el_comentario_de_admin_ya_exige_sesion_de_admin()
    {
        $this->post(route('comentario.reporte.admin', $this->reporte->id), ['comentario' => 'sin sesión'])
            ->assertRedirect(route('login'));

        $this->actingAs($this->usuario)
            ->post(route('comentario.reporte.admin', $this->reporte->id), ['comentario' => 'sin rol'])
            ->assertRedirect();

        $this->assertSame(0, Seguimiento::where('reporte_id', $this->reporte->id)->count());
    }

    public function test_el_admin_conserva_su_acceso_completo_al_detalle()
    {
        $admin = Admin::first();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('detalle.reporte.admin', $this->reporte->id));

        $response->assertStatus(200);
        $response->assertSee('falla de prueba del agente');
        $response->assertSee(route('admin.soporte'));
        $response->assertSee(route('comentario.reporte.admin', $this->reporte->id));
    }

    public function test_el_seeder_es_idempotente_y_respeta_la_clave_del_entorno()
    {
        putenv('SOPORTE_AGENTE_EMAIL=seeder@test.local');
        putenv('SOPORTE_AGENTE_PASSWORD=clave-de-prueba-123');
        putenv('SOPORTE_AGENTE_NOMBRE=Agente del Seeder');
        $_ENV['SOPORTE_AGENTE_PASSWORD'] = 'clave-de-prueba-123';
        $_ENV['SOPORTE_AGENTE_EMAIL'] = 'seeder@test.local';
        $_ENV['SOPORTE_AGENTE_NOMBRE'] = 'Agente del Seeder';

        try {
            $this->seed(AgenteSoporteSeeder::class);
            $this->seed(AgenteSoporteSeeder::class);

            $this->assertSame(1, AgenteSoporte::where('email', 'seeder@test.local')->count());

            $agente = AgenteSoporte::where('email', 'seeder@test.local')->first();
            $this->assertSame('Agente del Seeder', $agente->nombre);
            $this->assertTrue($agente->estaActivo());
            $this->assertTrue(
                Hash::check('clave-de-prueba-123', $agente->password)
            );

            // Al re-correrlo no debe pisar un agente ya gestionado desde el
            // panel: le cambiaria la clave y lo reactivaria.
            $agente->update(['nombre' => 'Editado en el Panel', 'password' => Hash::make('clave-del-panel'), 'activo' => false]);
            $this->seed(AgenteSoporteSeeder::class);

            $agente->refresh();
            $this->assertSame('Editado en el Panel', $agente->nombre);
            $this->assertFalse($agente->estaActivo());
            $this->assertTrue(Hash::check('clave-del-panel', $agente->password));
        } finally {
            putenv('SOPORTE_AGENTE_PASSWORD');
            putenv('SOPORTE_AGENTE_EMAIL');
            putenv('SOPORTE_AGENTE_NOMBRE');
            unset($_ENV['SOPORTE_AGENTE_PASSWORD'], $_ENV['SOPORTE_AGENTE_EMAIL'], $_ENV['SOPORTE_AGENTE_NOMBRE']);
        }
    }

    private function crearReporte($descripcion, $status, $userId = null)
    {
        return Reporte::create([
            'dispositivo' => 'computadora',
            'descripcion' => $descripcion,
            'status' => $status,
            'calificacion' => null,
            'observaciones' => null,
            'fecha_reporte' => Carbon::now()->format('Y-m-d'),
            'prioridad' => 'Alta',
            'fecha_solucion' => 'pendiente',
            'detalles_solucion' => 'pendiente',
            'user_id' => $userId ?? $this->usuario->id,
        ]);
    }

    private function crearUsuario($prefijo)
    {
        return User::create([
            'name' => $prefijo . ' ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'correo' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Prueba Soporte',
            'planta' => 'Planta Sur',
            'extension' => '9999',
            'celular' => 'No hay celular',
            'jefe' => 0,
            'resguardo_firmado' => 0,
            'activo' => 1,
        ]);
    }
}
