<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use App\Models\EvaluacionProveedor;
use App\Models\EvaluacionSeccion;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Resultados de evaluaciones agrupados por proveedor.
 *
 * Las fixtures se crean aqui: la pagina agrupa por mes y proveedor, asi que
 * depender de los datos reales haria el test fragil.
 */
class ResultadosViewTest extends TestCase
{
    use DatabaseTransactions;

    public function testPaginasResultadosCargan()
    {
        $admin = Admin::first();
        $intento = $this->intentoCompletado('2026-03-15');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.proveedores'))
            ->assertStatus(200);

        // El detalle solo existe para intentos completados.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.intento', $intento->id))
            ->assertStatus(200)
            ->assertSee($intento->proveedor->nombre);
    }

    public function testElDetalleDeUnIntentoNoCompletadoDa404()
    {
        $admin = Admin::first();
        $intento = $this->intentoCompletado('2026-03-15');
        $intento->estado = EvaluacionIntento::ESTADO_EN_PROGRESO;
        $intento->save();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.intento', $intento->id))
            ->assertNotFound();
    }

    public function testLaPaginaAgrupaPorProveedorYMes()
    {
        $admin = Admin::first();
        $intento = $this->intentoCompletado('2026-03-15', 80);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.proveedores'));

        $response->assertStatus(200);
        $response->assertSee('Marzo 2026');
        $response->assertSee('Promedio global: 80%');
        $response->assertSee(route('admin.resultados.intento', $intento->id), false);
    }

    public function testElFiltroPorMesAcotaLosResultados()
    {
        $admin = Admin::first();
        $marzo = $this->intentoCompletado('2026-03-15');
        $mayo = $this->intentoCompletado('2026-05-20');

        // El desplegable sigue listando todos los meses disponibles, asi que
        // para comprobar el acotado se miran los enlaces de detalle.
        $conDatos = $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.proveedores', ['mes' => '2026-05']));

        $conDatos->assertStatus(200);
        $conDatos->assertSee('Mostrando resultados del mes de');
        $conDatos->assertSee(route('admin.resultados.intento', $mayo->id), false);
        $conDatos->assertDontSee(route('admin.resultados.intento', $marzo->id), false);
    }

    public function testUnMesSinDatosSeIgnoraYSeMuestranTodos()
    {
        $admin = Admin::first();
        $marzo = $this->intentoCompletado('2026-03-15');

        // Un mes que no esta en el desplegable se descarta: la pagina cae en
        // "todos los meses" en vez de quedarse en blanco.
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.resultados.proveedores', ['mes' => '2026-07']));

        $response->assertStatus(200);
        $response->assertDontSee('Mostrando resultados del mes de');
        $response->assertSee(route('admin.resultados.intento', $marzo->id), false);
    }

    /**
     * Intento completado en el mes indicado, con su evaluacion, seccion y
     * proveedor. Devuelve el EvaluacionIntento.
     */
    private function intentoCompletado($fechaFinalizacion, $porcentaje = 100)
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor Resultado ' . uniqid(),
            'activo' => 1,
        ]);

        $evaluacion = Evaluacion::create([
            'titulo' => 'Evaluacion Resultado ' . uniqid(),
            'activo' => 1,
        ]);

        $seccion = EvaluacionSeccion::create([
            'evaluacion_id' => $evaluacion->id,
            'titulo' => 'Subencuesta Resultado',
            'periodicidad' => 30,
            'orden' => 1,
            'activo' => 1,
        ]);

        EvaluacionProveedor::create([
            'evaluacion_id' => $evaluacion->id,
            'proveedor_id' => $proveedor->id,
            'activo' => 1,
        ]);

        $usuario = User::create([
            'name' => 'Evaluador ' . uniqid(),
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

        return EvaluacionIntento::create([
            'evaluacion_id' => $evaluacion->id,
            'usuario_id' => $usuario->id,
            'proveedor_id' => $proveedor->id,
            'fecha_inicio' => Carbon::parse($fechaFinalizacion)->subDays(2),
            'fecha_finalizacion' => Carbon::parse($fechaFinalizacion),
            'puntuacion' => $porcentaje,
            'porcentaje' => $porcentaje,
            'estado' => EvaluacionIntento::ESTADO_COMPLETADO,
            'secciones_permitidas' => [$seccion->id],
            'comentario' => 'Comentario de prueba',
        ]);
    }
}
