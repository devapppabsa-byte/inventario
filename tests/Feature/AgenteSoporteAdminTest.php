<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AgenteSoporte;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alta, edición, activación y baja de agentes de soporte desde el panel admin.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea).
 */
class AgenteSoporteAdminTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;
    private $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::first();
        $this->usuario = User::create([
            'name' => 'Usuario Común ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'correo' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Prueba',
            'planta' => 'P1',
            'extension' => '100',
            'celular' => 'No hay celular',
            'jefe' => 0,
            'resguardo_firmado' => 0,
        ]);
    }

    public function test_la_gestion_exige_sesion_de_admin()
    {
        $this->get(route('admin.soporte.agentes'))->assertRedirect(route('login'));

        // Un usuario normal no alcanza el área.
        $this->actingAs($this->usuario)->get(route('admin.soporte.agentes'))->assertRedirect();

        // Tampoco un agente de soporte: su guard es otro.
        $agente = $this->crearAgente();
        $this->actingAs($agente, 'soporte')->get(route('admin.soporte.agentes'))->assertRedirect();
    }

    public function test_el_admin_registra_un_agent_con_la_contrasena_cifrada()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.soporte.agentes.store'), [
            'nombre' => 'Agente Nuevo',
            'email' => 'Nuevo.Agente@Test.Local',
            'password' => 'clave-larga-123',
            'activo' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('ok');

        $agente = AgenteSoporte::where('nombre', 'Agente Nuevo')->first();
        $this->assertNotNull($agente);
        $this->assertTrue($agente->estaActivo());

        // El email se guarda en minúsculas: el login lo compara exacto.
        $this->assertSame('nuevo.agente@test.local', $agente->email);

        // Y la contraseña no queda en claro.
        $this->assertNotSame('clave-larga-123', $agente->password);
        $this->assertTrue(Hash::check('clave-larga-123', $agente->password));
    }

    public function test_el_agente_creado_puede_entrar_al_sistema()
    {
        $this->actingAs($this->admin, 'admin')->post(route('admin.soporte.agentes.store'), [
            'nombre' => 'Agente Entrante',
            'email' => 'entrante@test.local',
            'password' => 'clave-larga-123',
            'activo' => 1,
        ]);

        $this->post(route('ingreso.admin'), [
            'email' => 'entrante@test.local',
            'password' => 'clave-larga-123',
            'tipo' => 'soporte',
        ])->assertRedirect(route('soporte.index'));

        $this->assertTrue(auth('soporte')->check());
    }

    public function test_no_se_pueden_registrar_dos_agentes_con_el_mismo_correo()
    {
        $this->crearAgente('ya.existe@test.local');

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.soporte.agentes'))
            ->post(route('admin.soporte.agentes.store'), [
                'nombre' => 'Duplicado',
                'email' => 'ya.existe@test.local',
                'password' => 'clave-larga-123',
            ]);

        $response->assertRedirect(route('admin.soporte.agentes'));
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, AgenteSoporte::where('email', 'ya.existe@test.local')->count());
    }

    public function test_la_contrasena_corta_se_rechaza()
    {
        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.soporte.agentes'))
            ->post(route('admin.soporte.agentes.store'), [
                'nombre' => 'Corta',
                'email' => 'corta@test.local',
                'password' => '1234567',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame(0, AgenteSoporte::where('email', 'corta@test.local')->count());
    }

    public function test_editar_sin_escribir_contrasena_conserva_la_clave_actual()
    {
        $agente = $this->crearAgente('conserva@test.local');

        $this->actingAs($this->admin, 'admin')->patch(route('admin.soporte.agentes.update', $agente->id), [
            'nombre' => 'Nombre Corregido',
            'email' => 'conserva@test.local',
            'password' => '',
            'activo' => 1,
        ])->assertSessionHas('ok');

        $agente->refresh();
        $this->assertSame('Nombre Corregido', $agente->nombre);
        $this->assertTrue(Hash::check('clave-inicial-123', $agente->password));

        // Y la clave sigue sirviendo para entrar.
        $this->post(route('ingreso.admin'), [
            'email' => 'conserva@test.local',
            'password' => 'clave-inicial-123',
            'tipo' => 'soporte',
        ])->assertRedirect(route('soporte.index'));
        $this->assertTrue(auth('soporte')->check());
    }

    public function test_editar_con_contrasena_nueva_cambia_la_clave()
    {
        $agente = $this->crearAgente('cambia@test.local');

        $this->actingAs($this->admin, 'admin')->patch(route('admin.soporte.agentes.update', $agente->id), [
            'nombre' => $agente->nombre,
            'email' => $agente->email,
            'password' => 'contrasena-nueva-999',
        ])->assertSessionHas('ok');

        $agente->refresh();
        $this->assertTrue(Hash::check('contrasena-nueva-999', $agente->password));
        $this->assertFalse(Hash::check('clave-inicial-123', $agente->password));
    }

    public function test_al_editar_no_se_puede_tomar_el_correo_de_otro_agente_pero_si_el_propio()
    {
        $agente = $this->crearAgente('uno@test.local');
        $this->crearAgente('dos@test.local');

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.soporte.agentes'))
            ->patch(route('admin.soporte.agentes.update', $agente->id), [
                'nombre' => $agente->nombre,
                'email' => 'dos@test.local',
            ])
            ->assertSessionHasErrors('email');

        // El propio correo sí se acepta: no es un conflicto consigo mismo.
        $this->actingAs($this->admin, 'admin')->patch(route('admin.soporte.agentes.update', $agente->id), [
            'nombre' => 'Uno Renombrado',
            'email' => 'uno@test.local',
        ])->assertSessionHasNoErrors();
    }

    public function test_el_admin_puede_desactivar_y_reactivar_un_agente()
    {
        $agente = $this->crearAgente('alterna@test.local');

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.soporte.agentes.toggle', $agente->id))
            ->assertSessionHas('ok');
        $this->assertFalse($agente->fresh()->estaActivo());

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.soporte.agentes.toggle', $agente->id));
        $this->assertTrue($agente->fresh()->estaActivo());
    }

    public function test_el_admin_puede_eliminar_un_agente()
    {
        $agente = $this->crearAgente('se.va@test.local');

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.soporte.agentes.destroy', $agente->id))
            ->assertSessionHas('ok');

        $this->assertNull(AgenteSoporte::find($agente->id));
    }

    public function test_un_agente_desactivado_no_puede_iniciar_sesion()
    {
        $agente = $this->crearAgente('inactivo@test.local');
        $agente->update(['activo' => false]);

        $this->post(route('ingreso.admin'), [
            'email' => 'inactivo@test.local',
            'password' => 'clave-inicial-123',
            'tipo' => 'soporte',
        ])->assertRedirect();

        $this->assertFalse(auth('soporte')->check());
    }

    public function test_desactivar_corta_el_aceso_de_una_sesion_ya_abierta()
    {
        $agente = $this->crearAgente('sesion.viva@test.local');

        $this->actingAs($agente, 'soporte')->get(route('soporte.index'))->assertStatus(200);

        // El administrador lo desactiva mientras el agente sigue conectado.
        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.soporte.agentes.toggle', $agente->id));

        // Su sesión abierta ya no le da acceso.
        $this->get(route('soporte.index'))->assertRedirect(route('login'));
    }

    public function test_el_admin_ve_los_agentes_con_estado_y_buscador()
    {
        $this->crearAgente('buscable@test.local', 'Persona Buscable');
        $this->crearAgente('otro@test.local', 'Nombre Distinto')->update(['activo' => false]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.soporte.agentes'));
        $response->assertStatus(200);
        $response->assertSee('Persona Buscable');
        $response->assertSee('Inactivo');

        $filtrado = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.soporte.agentes', ['busqueda' => 'buscable']));
        $filtrado->assertStatus(200);
        $filtrado->assertSee('Persona Buscable');
        $filtrado->assertDontSee('Nombre Distinto');

        $this->assertStringContainsString(route('admin.soporte.agentes'), $response->getContent());
    }

    public function test_el_enlace_de_agentes_no_aparece_en_el_menu_del_agente()
    {
        $agente = $this->crearAgente('menu.agente@test.local');

        $response = $this->actingAs($agente, 'soporte')->get(route('soporte.index'));
        $response->assertStatus(200);
        $response->assertDontSee(route('admin.soporte.agentes'));
        $response->assertDontSee(route('admin.soporte'));
    }

    private function crearAgente($email = null, $nombre = null)
    {
        return AgenteSoporte::create([
            'nombre' => $nombre ?? ('Agente ' . uniqid()),
            'email' => $email ?? (uniqid() . '@test.local'),
            'password' => Hash::make('clave-inicial-123'),
            'activo' => true,
        ]);
    }
}
