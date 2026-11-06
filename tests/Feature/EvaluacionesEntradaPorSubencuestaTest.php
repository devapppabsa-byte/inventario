<?php

namespace Tests\Feature;

use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use App\Models\EvaluacionOpcion;
use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionProveedor;
use App\Models\EvaluacionRespuesta;
use App\Models\EvaluacionSeccion;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Entrada por subencueta: al hacer clic en una se abre solo esa.
 *
 * OJO: phpunit.xml corre contra la BD real (el sqlite esta comentado), por eso
 * se usa DatabaseTransactions y NUNCA RefreshDatabase, que la dejaria vacia.
 */
class EvaluacionesEntradaPorSubencuestaTest extends TestCase
{
    use DatabaseTransactions;

    private $evaluacion;
    private $proveedor;
    private $usuario;
    private $secciones = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->proveedor = Proveedor::create([
            'nombre' => 'Proveedor test ' . uniqid(),
            'activo' => true,
        ]);

        $this->evaluacion = Evaluacion::create([
            'titulo' => 'Evaluacion test ' . uniqid(),
            'tipo_usuario' => 'Usuarios',
            'activo' => true,
        ]);

        EvaluacionProveedor::create([
            'evaluacion_id' => $this->evaluacion->id,
            'proveedor_id' => $this->proveedor->id,
            'activo' => true,
        ]);

        $this->secciones['trimestral'] = $this->crearSeccion('Subencuesta trimestral', 'trimestral');
        $this->secciones['bimestral'] = $this->crearSeccion('Subencueta bimestral', 'bimestral');
        $this->secciones['semestral'] = $this->crearSeccion('Subencueta semestral', 'semestral');

        $this->usuario = User::create([
            'name' => 'Usuario test ' . uniqid(),
            'email' => uniqid() . '@test.local',
            'password' => bcrypt('secret1234'),
            'puesto' => 'Puesto de prueba',
            'planta' => 'P3',
            'extension' => '100',
            'jefe' => 0,
            'resguardo_firmado' => 0,
            'activo' => true,
        ]);
    }

    public function test_la_vista_enlaza_la_subencueta_disponible_y_no_la_bloqueada()
    {
        $this->asignar([$this->secciones['trimestral']->id, $this->secciones['bimestral']->id]);
        $this->completar([$this->secciones['trimestral']->id]);

        $html = $this->actingAs($this->usuario)->get(route('evaluaciones.mis'))->getContent();

        $urlDisponible = route('evaluaciones.responderSeccion', [$this->evaluacion->id, $this->secciones['bimestral']->id]);
        $urlBloqueada = route('evaluaciones.responderSeccion', [$this->evaluacion->id, $this->secciones['trimestral']->id]);

        $this->assertStringContainsString($urlDisponible, $html, 'La subencueta disponible debe ser enlazable');
        $this->assertStringNotContainsString($urlBloqueada, $html, 'La subencueta bloqueada no debe ser enlazable');
        // La entrada es solo por fila: no hay boton global para responder todo.
        $this->assertStringNotContainsString('Responder todo lo pendiente', $html);
        $this->assertStringNotContainsString('periodicidadLabel', $html);
    }

    public function test_entrar_por_una_subencueta_crea_el_intento_solo_con_ella()
    {
        $seccion = $this->secciones['bimestral'];
        $this->asignar([$seccion->id, $this->secciones['trimestral']->id]);

        $response = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.responderSeccion', [$this->evaluacion->id, $seccion->id]));

        $response->assertStatus(200);
        $response->assertSee($seccion->titulo);

        $this->actingAs($this->usuario)
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), [
                'proveedor_id' => $this->proveedor->id,
                'seccion_id' => $seccion->id,
            ]);

        $intento = EvaluacionIntento::where('usuario_id', $this->usuario->id)->firstOrFail();
        $this->assertSame([(int) $seccion->id], $intento->seccionesPermitidasIds());
    }

    public function test_una_subencueta_bloqueada_se_rechaza_por_url_directa()
    {
        $bloqueada = $this->secciones['trimestral'];
        $this->asignar([$bloqueada->id]);
        $this->completar([$bloqueada->id]);

        $response = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.responderSeccion', [$this->evaluacion->id, $bloqueada->id]));

        $response->assertRedirect(route('evaluaciones.mis'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString($bloqueada->titulo, session('error'));
        $this->assertStringContainsString(
            Carbon::now()->addDays(90)->format('d/m/Y'),
            session('error'),
            'El aviso debe decir hasta que fecha se reabre'
        );
    }

    public function test_una_subencueta_no_asignada_se_rechaza()
    {
        $this->asignar([$this->secciones['bimestral']->id]);

        $response = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.responderSeccion', [$this->evaluacion->id, $this->secciones['trimestral']->id]));

        $response->assertRedirect(route('evaluaciones.mis'));
        $this->assertStringContainsString('No tienes asignada', session('error'));
    }

    public function test_no_se_puede_forzar_una_subencueta_bloqueada_enviando_seccion_id()
    {
        $bloqueada = $this->secciones['trimestral'];
        $this->asignar([$bloqueada->id, $this->secciones['bimestral']->id]);
        $this->completar([$bloqueada->id]);

        $this->actingAs($this->usuario)
            ->from(route('evaluaciones.mis'))
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), [
                'proveedor_id' => $this->proveedor->id,
                'seccion_id' => $bloqueada->id,
            ])
            ->assertSessionHas('error');

        $this->assertSame(
            1,
            EvaluacionIntento::where('usuario_id', $this->usuario->id)->count(),
            'No debe crearse un intento con la subencueta bloqueada'
        );
    }

    public function test_la_pagina_responder_crea_el_intento_con_todas_las_pendientes()
    {
        $this->asignar([
            $this->secciones['trimestral']->id,
            $this->secciones['bimestral']->id,
            $this->secciones['semestral']->id,
        ]);
        $this->completar([$this->secciones['trimestral']->id]);

        $response = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.responder', $this->evaluacion->id));
        $response->assertStatus(200);
        $response->assertSee('Subencueta bimestral');
        $response->assertSee('Subencueta semestral');

        $this->actingAs($this->usuario)
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), [
                'proveedor_id' => $this->proveedor->id,
            ]);

        $intento = EvaluacionIntento::where('usuario_id', $this->usuario->id)->orderBy('id', 'desc')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [(int) $this->secciones['bimestral']->id, (int) $this->secciones['semestral']->id],
            $intento->seccionesPermitidasIds()
        );
    }

    public function test_el_intento_caduca_segun_su_propia_subencueta()
    {
        // La evaluacion tiene una subencueta semestral, pero el intento es trimestral:
        // debe caducar a los 90 dias, no a los 180 de la subencueta mas larga.
        $trimestral = $this->secciones['trimestral'];

        $vencido = $this->intentoEnProgreso([$trimestral->id], Carbon::now()->subDays(100));
        $vigente = $this->intentoEnProgreso([$trimestral->id], Carbon::now()->subDays(80));

        $this->assertTrue($vencido->esVencido(), 'A los 100 dias un intento trimestral debe estar vencido');
        $this->assertFalse($vigente->esVencido(), 'A los 80 dias todavia esta vigente');
        $this->assertSame(90, $vencido->periodoDiasMaximo());
    }

    public function test_el_resultado_de_admin_muestra_solo_las_subencuetas_del_intento()
    {
        $trimestral = $this->secciones['trimestral'];
        $this->asignar([$trimestral->id, $this->secciones['bimestral']->id]);
        $intento = $this->completar([$trimestral->id]);

        $html = $this->actingAs(\App\Models\Admin::first(), 'admin')
            ->get(route('admin.resultados.intento', $intento->id))
            ->getContent();

        $this->assertStringContainsString($trimestral->titulo, $html);
        $this->assertStringNotContainsString(
            $this->secciones['bimestral']->titulo,
            $html,
            'No debe mostrar subencuetas que el intento no cubrio'
        );
    }

    private function crearSeccion($titulo, $periodicidad, $preguntas = 2)
    {
        $seccion = EvaluacionSeccion::create([
            'evaluacion_id' => $this->evaluacion->id,
            'titulo' => $titulo,
            'periodicidad' => $periodicidad,
            'orden' => EvaluacionSeccion::where('evaluacion_id', $this->evaluacion->id)->count() + 1,
            'activo' => true,
        ]);

        for ($i = 1; $i <= $preguntas; $i++) {
            $pregunta = EvaluacionPregunta::create([
                'seccion_id' => $seccion->id,
                'pregunta' => $titulo . ' pregunta ' . $i,
                'puntos_maximos' => 10,
                'orden' => $i,
                'activo' => true,
            ]);
            EvaluacionOpcion::create([
                'pregunta_id' => $pregunta->id,
                'texto' => 'Opcion ' . $i,
                'puntos' => 5,
                'valor' => $i,
                'orden' => 1,
                'activo' => true,
            ]);
        }

        return $seccion;
    }

    private function asignar(array $seccionesIds)
    {
        $this->usuario->evaluaciones()->syncWithoutDetaching([
            $this->evaluacion->id => ['activo' => true, 'secciones' => json_encode(array_values($seccionesIds))],
        ]);
    }

    private function completar(array $seccionesIds)
    {
        $intento = $this->intentoEnProgreso($seccionesIds, null);
        $intento->estado = EvaluacionIntento::ESTADO_COMPLETADO;
        $intento->fecha_finalizacion = Carbon::now();
        $intento->save();

        foreach ($seccionesIds as $seccionId) {
            $preguntas = EvaluacionPregunta::where('seccion_id', $seccionId)->where('activo', true)->get();
            foreach ($preguntas as $pregunta) {
                EvaluacionRespuesta::create([
                    'intento_id' => $intento->id,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => EvaluacionOpcion::where('pregunta_id', $pregunta->id)->value('id'),
                    'puntos_obtenidos' => 5,
                ]);
            }
        }

        return $intento;
    }

    private function intentoEnProgreso(array $seccionesIds, $fechaInicio = null)
    {
        return EvaluacionIntento::create([
            'evaluacion_id' => $this->evaluacion->id,
            'usuario_id' => $this->usuario->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => $fechaInicio ?: Carbon::now(),
            'estado' => EvaluacionIntento::ESTADO_EN_PROGRESO,
            'secciones_permitidas' => array_values($seccionesIds),
        ]);
    }
}
