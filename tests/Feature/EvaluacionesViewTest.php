<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Evaluacion;
use App\Models\EvaluacionSeccion;
use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionOpcion;
use App\Models\EvaluacionProveedor;
use App\Models\EvaluacionUsuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EvaluacionesViewTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Usuario propio con todas las secciones de la evaluacion asignadas.
     *
     * No se usa User::first(): los datos reales los edita el admin a mano y
     * una asignacion vacia o unos intentos viejos hacian fallar el flujo.
     */
    private function usuarioConEvaluacion(Evaluacion $evaluacion)
    {
        $user = User::create([
            'name' => 'Usuario Evaluacion ' . uniqid(),
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

        EvaluacionUsuario::create([
            'usuario_id' => $user->id,
            'evaluacion_id' => $evaluacion->id,
            'activo' => 1,
            'secciones' => EvaluacionSeccion::where('evaluacion_id', $evaluacion->id)->pluck('id')->all(),
        ]);

        return $user;
    }

    public function testPaginasAdminCargan()
    {
        $admin = Admin::first();
        $response = $this->actingAs($admin, 'admin')->get(route('admin.proveedores'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.evaluaciones'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.evaluaciones.create'));
        $response->assertStatus(200);

        $evaluacion = Evaluacion::first();
        if ($evaluacion) {
            $response = $this->actingAs($admin, 'admin')->get(route('admin.evaluaciones.show', $evaluacion->id));
            $response->assertStatus(200);
            $response->assertSee($evaluacion->titulo);
            // Verificar que aparece el badge de periodicidad de alguna seccion
            $seccion = EvaluacionSeccion::where('evaluacion_id', $evaluacion->id)->first();
            if ($seccion) {
                $response->assertSee(ucfirst($seccion->periodicidad), false);
            }
        }

        $response = $this->actingAs($admin, 'admin')->get(route('agregar.usuarios'));
        $response->assertStatus(200);
    }

    public function testPaginasUsuarioCargan()
    {
        $evaluacion = Evaluacion::first();
        $user = $this->usuarioConEvaluacion($evaluacion);

        $response = $this->actingAs($user)->get(route('evaluaciones.mis'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('evaluaciones.responder', $evaluacion->id));
        $response->assertStatus(200);
    }

    public function testFlujoCompletoEvaluacion()
    {
        $evaluacion = Evaluacion::first();
        $user = $this->usuarioConEvaluacion($evaluacion);
        $proveedor = EvaluacionProveedor::where('evaluacion_id', $evaluacion->id)->first();

        // Iniciar intento
        $response = $this->actingAs($user)->post(route('evaluaciones.iniciar', $evaluacion->id), [
            'proveedor_id' => $proveedor->proveedor_id,
        ]);
        $response->assertRedirect();

        // El recien creado, no el mas antiguo: pueden quedar intentos viejos.
        $intento = \App\Models\EvaluacionIntento::where('usuario_id', $user->id)
            ->orderByDesc('id')->first();
        $this->assertNotNull($intento);
        $this->assertNotEmpty(
            $intento->secciones_permitidas,
            'El intento debe copiar las subencuetas disponibles al iniciarlo'
        );

        // Acceder al formulario
        $response = $this->actingAs($user)->get(route('evaluaciones.intento', $intento->id));
        $response->assertStatus(200);

        // Responder todas las preguntas con la primera opcion (3 pts)
        $preguntas = EvaluacionPregunta::whereHas('seccion', function($q) use ($evaluacion){
            $q->where('evaluacion_id', $evaluacion->id);
        })->get();

        $data = [];
        foreach ($preguntas as $pregunta) {
            $opcion = EvaluacionOpcion::where('pregunta_id', $pregunta->id)->first();
            $data['pregunta_' . $pregunta->id] = $opcion->id;
        }
        $data['comentario'] = 'Excelente proveedor';
        $data['action'] = 'finalizar';

        $response = $this->actingAs($user)->post(route('evaluaciones.guardar', $intento->id), $data);
        $response->assertRedirect();

        // Verificar que se completo
        $intento->refresh();
        $this->assertEquals('completado', $intento->estado);
        $this->assertEquals(100.0, (float) $intento->porcentaje);
        $this->assertEquals('Excelente proveedor', $intento->comentario);

        // Acceder a resultado
        $response = $this->actingAs($user)->get(route('evaluaciones.resultado', $intento->id));
        $response->assertStatus(200);
    }
}
