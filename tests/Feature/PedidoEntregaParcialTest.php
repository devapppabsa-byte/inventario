<?php

namespace Tests\Feature;

use App\Mail\PedidoTinta;
use App\Models\Admin;
use App\Models\EmailSetting;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Entrega parcial de tintas y pedido residual.
 *
 * Al marcar un pedido como "no se entrego todo", se registra quien lo marco
 * y se genera un pedido residual unicamente con lo que falto (columna
 * parent_id), para que tambien se pueda completar despues. Cuando el
 * residual se completa, el original deja de ser parcial.
 *
 * OJO: phpunit.xml apunta a la BD real, asi que DatabaseTransactions y nunca
 * RefreshDatabase (tests/TestCase.php lo bloquea).
 */
class PedidoEntregaParcialTest extends TestCase
{
    use DatabaseTransactions;

    private $usuario;
    private $otro;
    private $admin;

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

        $this->usuario = $this->crearUsuario('Parcial');
        $this->otro = $this->crearUsuario('Ajeno');
        $this->admin = Admin::first();
    }

    // ---------------------------------------------------------------- helpers

    private function crearUsuario(string $prefijo): User
    {
        return User::create([
            'name' => $prefijo.' '.uniqid(),
            'email' => uniqid().'@test.local',
            'correo' => uniqid().'@test.local',
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

    private function crearPedido(User $usuario, string $numero = 'Otra', string $colores = '["Negro","Azul","Amarillo"]'): Pedido
    {
        return Pedido::create([
            'numero' => $numero,
            'colores' => $colores,
            'user_id' => $usuario->id,
            'fecha_pedido' => '2026-10-07',
            'marca' => 'HP',
            'cantidad' => null,
            'foto_tintas' => 'public/fake.png',
        ]);
    }

    private function comoAdmin()
    {
        return $this->actingAs($this->admin, 'admin');
    }

    // ---------------------------------------------------------------- admin: total

    public function testAdminMarcaTodoYRegistraQuien()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'total',
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('completado');

        $pedido->refresh();
        $this->assertSame('Completado', $pedido->status);
        $this->assertSame($this->admin->nombre, $pedido->completado_por);
        $this->assertNotNull($pedido->fecha_entrega);
        $this->assertSame(0, Pedido::where('parent_id', $pedido->id)->count());
    }

    // ---------------------------------------------------------------- admin: parcial

    public function testAdminMarcaParcialYGeneraResidual()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('completado');

        $pedido->refresh();
        $this->assertSame('Entrega parcial', $pedido->status);
        $this->assertSame($this->admin->nombre, $pedido->completado_por);

        $residual = Pedido::where('parent_id', $pedido->id)->first();
        $this->assertNotNull($residual);
        $this->assertSame(['Azul'], json_decode($residual->colores, true));
        $this->assertSame('pendiente', $residual->status);
        $this->assertSame($this->usuario->id, (int) $residual->user_id);
        $this->assertSame($pedido->numero, $residual->numero);

        Mail::assertSent(PedidoTinta::class, 1);
    }

    public function testAdminParcialSinTintasFallaYSinResidual()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()
            ->from(route('admin.pedidos.tintas'))
            ->patch(route('admin.pedidos.completar', $pedido->id), ['entrega' => 'parcial']);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHasErrors('pendientes');

        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
        $this->assertSame(0, Pedido::where('parent_id', $pedido->id)->count());
    }

    public function testAdminParcialConTodasLasTintasSeRechaza()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()
            ->from(route('admin.pedidos.tintas'))
            ->patch(route('admin.pedidos.completar', $pedido->id), [
                'entrega' => 'parcial',
                'pendientes' => ['Negro', 'Azul', 'Amarillo'],
            ]);

        $respuesta->assertSessionHasErrors('pendientes');

        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
        $this->assertSame(0, Pedido::where('parent_id', $pedido->id)->count());
    }

    public function testAdminParcialConTintaAjenaSeRechaza()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()
            ->from(route('admin.pedidos.tintas'))
            ->patch(route('admin.pedidos.completar', $pedido->id), [
                'entrega' => 'parcial',
                'pendientes' => ['Magenta'],
            ]);

        $respuesta->assertSessionHasErrors('pendientes');

        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
        $this->assertSame(0, Pedido::where('parent_id', $pedido->id)->count());
    }

    // ---------------------------------------------------------------- Cintas

    public function testAdminParcialEnCintasGeneraResidualConCantidad()
    {
        $pedido = $this->crearPedido($this->usuario, 'Cintas', '[3]');

        $respuesta = $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'cantidad_pendiente' => 1,
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('completado');

        $pedido->refresh();
        $this->assertSame('Entrega parcial', $pedido->status);

        $residual = Pedido::where('parent_id', $pedido->id)->first();
        $this->assertNotNull($residual);
        $this->assertSame('[1]', $residual->colores);
    }

    public function testAdminParcialEnCintasConMasDeLasPedidasSeRechaza()
    {
        $pedido = $this->crearPedido($this->usuario, 'Cintas', '[3]');

        $respuesta = $this->comoAdmin()
            ->from(route('admin.pedidos.tintas'))
            ->patch(route('admin.pedidos.completar', $pedido->id), [
                'entrega' => 'parcial',
                'cantidad_pendiente' => 5,
            ]);

        $respuesta->assertSessionHasErrors('cantidad_pendiente');

        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
    }

    // ---------------------------------------------------------------- idempotencia y null

    public function testSegundoClicNoDuplicaNiPisa()
    {
        $pedido = $this->crearPedido($this->usuario);

        $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), ['entrega' => 'total']);
        $pedido->refresh();
        $fecha = $pedido->fecha_entrega;
        $quien = $pedido->completado_por;

        $segundo = $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $segundo->assertSessionHas('completado');

        $pedido->refresh();
        $this->assertSame('Completado', $pedido->status);
        $this->assertSame($fecha, $pedido->fecha_entrega);
        $this->assertSame($quien, $pedido->completado_por);
        $this->assertSame(0, Pedido::where('parent_id', $pedido->id)->count());
    }

    public function testStatusNuloSePuedeCompletar()
    {
        $pedido = $this->crearPedido($this->usuario);
        \DB::table('pedidos')->where('id', $pedido->id)->update(['status' => null]);

        $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), ['entrega' => 'total']);

        $pedido->refresh();
        $this->assertSame('Completado', $pedido->status);
    }

    // ---------------------------------------------------------------- usuario

    public function testDuenoMarcaParcialYRegistraSuNombre()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->actingAs($this->usuario)
            ->patch(route('pedido.completo', $pedido->id), [
                'entrega' => 'parcial',
                'pendientes' => ['Amarillo'],
            ]);

        $respuesta->assertRedirect();
        $pedido->refresh();
        $this->assertSame('Entrega parcial', $pedido->status);
        $this->assertSame($this->usuario->name, $pedido->completado_por);
        $this->assertNotNull(Pedido::where('parent_id', $pedido->id)->first());
    }

    public function testUsuarioAjenoRecibe403()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->actingAs($this->otro)
            ->patch(route('pedido.completo', $pedido->id), ['entrega' => 'total']);

        $respuesta->assertForbidden();

        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
    }

    public function testUsuarioNormalNoAlcanzaRutaAdmin()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->actingAs($this->usuario)
            ->patch(route('admin.pedidos.completar', $pedido->id), ['entrega' => 'total']);

        $respuesta->assertStatus(302);
        $pedido->refresh();
        $this->assertSame('pendiente', $pedido->status);
    }

    public function testCorreoDelResidualAvisaYMuestraElOriginal()
    {
        $pedido = $this->crearPedido($this->usuario);

        $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $residual = Pedido::where('parent_id', $pedido->id)->first();
        $this->assertNotNull($residual);

        $correo = (new PedidoTinta($residual, 'tintas@test.local', 'Tintas'))->build();
        $this->assertStringContainsString('[Residual del pedido #' . $pedido->id . ']', $correo->subject);
        $this->assertStringContainsString('#' . $residual->id, $correo->subject);

        $mensaje = new class {
            public function embed($ruta)
            {
                return 'cid:' . md5($ruta);
            }
        };
        $html = view('emails.pedido_tinta', [
            'pedido' => $residual,
            'foto_path' => Storage::disk('local')->path($residual->foto_tintas),
            'message' => $mensaje,
        ])->render();

        $this->assertStringContainsString('Pedido residual del pedido #' . $pedido->id, $html);
    }

    public function testCorreoNormalNoLlevaAvisoDeResidual()
    {
        $pedido = $this->crearPedido($this->usuario);

        $correo = (new PedidoTinta($pedido, 'tintas@test.local', 'Tintas'))->build();
        $this->assertStringNotContainsString('Residual', $correo->subject);
    }

    // ---------------------------------------------------------------- cadena: residual -> original

    public function testAlCompletarResidualElOriginalSeAutoCompleta()
    {
        $pedido = $this->crearPedido($this->usuario);

        $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $pedido->refresh();
        $quienParcial = $pedido->completado_por;
        $fechaParcial = $pedido->fecha_entrega;
        $residual = Pedido::where('parent_id', $pedido->id)->first();

        $this->actingAs($this->usuario)
            ->patch(route('pedido.completo', $residual->id), ['entrega' => 'total']);

        $residual->refresh();
        $pedido->refresh();

        $this->assertSame('Completado', $residual->status);
        $this->assertSame('Completado', $pedido->status);
        $this->assertSame($quienParcial, $pedido->completado_por);
        $this->assertSame($fechaParcial, $pedido->fecha_entrega);
    }

    // ---------------------------------------------------------------- respuesta.admin

    public function testRespuestaAdminSinSesionRedirigeYSinCambios()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->patch(route('respuesta.admin', $pedido->id), ['respuesta_admin' => 'ok']);

        $respuesta->assertRedirect();
        $pedido->refresh();
        $this->assertNull($pedido->respuesta_admin);
    }

    public function testRespuestaAdminConSesionGuarda()
    {
        $pedido = $this->crearPedido($this->usuario);

        $respuesta = $this->comoAdmin()
            ->patch(route('respuesta.admin', $pedido->id), ['respuesta_admin' => 'Recibido']);

        $respuesta->assertRedirect();
        $pedido->refresh();
        $this->assertSame('Recibido', $pedido->respuesta_admin);
    }

    // ---------------------------------------------------------------- vistas y tarjetas

    public function testVistaAdminMuestraEstadoResidualYConteos()
    {
        $totalAntes = Pedido::count();
        $parcialesAntes = Pedido::where('status', 'Entrega parcial')->count();

        $pedido = $this->crearPedido($this->usuario);

        $this->comoAdmin()->patch(route('admin.pedidos.completar', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $respuesta = $this->comoAdmin()->get(route('admin.pedidos.tintas'));
        $respuesta->assertStatus(200);

        $contenido = $respuesta->getContent();
        $this->assertStringContainsString('<th>Estado</th>', $contenido);
        $this->assertStringContainsString('<th>Completado por</th>', $contenido);
        $this->assertStringContainsString('<th>Residuales</th>', $contenido);
        $this->assertStringContainsString('Entrega parcial', $contenido);
        $this->assertStringContainsString('Faltan: Azul', $contenido);
        $this->assertStringContainsString('Residual de #'.$pedido->id, $contenido);
        $this->assertStringContainsString('1 residual', $contenido);
        $this->assertStringContainsString('id="btn-generar-'.$pedido->id.'"', $contenido);
        $this->assertStringContainsString('validarParcial('.$pedido->id.')', $contenido);
        $this->assertStringContainsString('function validarParcial', $contenido);
        $this->assertStringNotContainsString("b.type = 'submit'", $contenido);

        $datos = $respuesta->original->getData();
        // El residual cuenta en el total como un pedido mas.
        $this->assertSame($totalAntes + 2, $datos['totalPedidos']);
        $this->assertSame($parcialesAntes + 1, $datos['parciales']);

        $top = collect($datos['topUsuarios'])->firstWhere('user_id', $this->usuario->id);
        $this->assertSame(
            Pedido::where('user_id', $this->usuario->id)->count(),
            (int) $top->n
        );
    }

    public function testColspanVacioEsNueve()
    {
        $respuesta = $this->comoAdmin()->get(route('admin.pedidos.tintas', [
            'desde' => '2099-01-01',
            'hasta' => '2099-12-31',
        ]));

        $respuesta->assertStatus(200);
        $this->assertStringContainsString('colspan="9"', $respuesta->getContent());
    }

    public function testVistaUsuarioMuestraFaltanYLuegoQuedaron()
    {
        $pedido = $this->crearPedido($this->usuario, 'Otra', '["Negro","Azul"]');

        $this->actingAs($this->usuario)->patch(route('pedido.completo', $pedido->id), [
            'entrega' => 'parcial',
            'pendientes' => ['Azul'],
        ]);

        $vista = $this->actingAs($this->usuario)->get(route('tintas.show'));
        $vista->assertStatus(200);
        $this->assertStringContainsString('Entrega parcial', $vista->getContent());
        $this->assertStringContainsString('Faltan: Azul', $vista->getContent());
        $this->assertStringContainsString('Continuación de #'.$pedido->id, $vista->getContent());

        $residual = Pedido::where('parent_id', $pedido->id)->first();
        $this->actingAs($this->usuario)
            ->patch(route('pedido.completo', $residual->id), ['entrega' => 'total']);

        $vista = $this->actingAs($this->usuario)->get(route('tintas.show'));
        $vista->assertStatus(200);
        $this->assertStringContainsString('Quedaron: Azul', $vista->getContent());
        $this->assertStringContainsString('function validarParcial', $vista->getContent());
        $this->assertStringNotContainsString("b.type = 'submit'", $vista->getContent());
    }
}
