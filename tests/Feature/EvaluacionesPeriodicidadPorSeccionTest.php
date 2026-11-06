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
 * La periodicidad se aplica por subencueta, no sobre la evaluacion completa.
 *
 * OJO: phpunit.xml corre contra la BD real (el sqlite esta comentado), por eso
 * se usa DatabaseTransactions y NUNCA RefreshDatabase, que borraria los datos.
 */
class EvaluacionesPeriodicidadPorSeccionTest extends TestCase
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
            'periodicidad' => 'trimestral',
            'tipo_usuario' => 'usuario',
            'activo' => true,
        ]);

        EvaluacionProveedor::create([
            'evaluacion_id' => $this->evaluacion->id,
            'proveedor_id' => $this->proveedor->id,
            'activo' => true,
        ]);

        // Semestral: si el bloqueo tomara el maximo de las secciones, contaminaria
        // a la trimestral y la dejaria bloqueada 180 dias en vez de 90.
        $this->secciones['semestral'] = $this->crearSeccion('Seccion semestral', 'semestral');
        $this->secciones['trimestral'] = $this->crearSeccion('Seccion trimestral', 'trimestral');
        $this->secciones['bimestral'] = $this->crearSeccion('Seccion bimestral', 'bimestral');
        $this->secciones['sin_preguntas'] = $this->crearSeccion('Seccion sin preguntas', 'mensual', 0);

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

    public function test_una_subencueta_pendiente_impide_finalmente_responder()
    {
        $this->asignarSecciones([$this->secciones['trimestral']->id]);

        $this->completarIntento([$this->secciones['trimestral']->id], finalizadaEn: Carbon::now()->subDays(3));

        // El admin agrega una subencueta nueva despues de que el usuario ya contesto.
        $this->asignarSecciones([
            $this->secciones['trimestral']->id,
            $this->secciones['bimestral']->id,
        ]);

        $estado = $this->estadoEnPerfil();

        $this->assertTrue($estado['trimestral'] === 'bloqueada', 'La subencueta contestada debe seguir bloqueada');
        $this->assertSame('disponible', $estado['bimestral'], 'La subencueta nueva debe estar disponible');
        $this->assertTrue($this->tienePendientes(), 'Debe quedar pendiente al menos una subencueta');
    }

    public function test_la_bloqueada_usa_su_propia_periodicidad_y_no_la_mayor_de_la_evaluacion()
    {
        $this->asignarSecciones([$this->secciones['trimestral']->id]);
        $finalizadaEn = Carbon::now()->subDays(3);
        $this->completarIntento([$this->secciones['trimestral']->id], finalizadaEn: $finalizadaEn);

        $limite = $this->limiteDe('trimestral');

        $this->assertNotNull($limite, 'La subencueta contestada debe tener fecha limite');
        $this->assertSame(
            $finalizadaEn->copy()->addDays(90)->format('Y-m-d H:i'),
            $limite->format('Y-m-d H:i'),
            'Debe bloquear 90 dias (trimestral) desde la finalizacion, no 180 (el maximo de la evaluacion)'
        );
    }

    public function test_el_nuevo_intento_se_crea_solo_con_las_subencuetas_disponibles()
    {
        $this->asignarSecciones([$this->secciones['trimestral']->id]);
        $this->completarIntento([$this->secciones['trimestral']->id]);

        $this->asignarSecciones([
            $this->secciones['trimestral']->id,
            $this->secciones['bimestral']->id,
            $this->secciones['semestral']->id,
        ]);

        $response = $this->actingAs($this->usuario)
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), ['proveedor_id' => $this->proveedor->id]);

        $intento = EvaluacionIntento::where('usuario_id', $this->usuario->id)
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($intento);
        $response->assertRedirect(route('evaluaciones.intento', $intento->id));
        $this->assertEqualsCanonicalizing(
            [
                (int) $this->secciones['bimestral']->id,
                (int) $this->secciones['semestral']->id,
            ],
            $intento->seccionesPermitidasIds(),
            'El intento debe llevar solo las subencuetas disponibles, no la ya contestada'
        );
    }

    public function test_una_subencueta_respondida_a_medias_no_queda_bloqueada()
    {
        $seccion = $this->secciones['trimestral'];
        $preguntas = $seccion->preguntasActivas()->get();

        $this->asignarSecciones([$seccion->id]);

        // Finaliza respondiendo solo la primera de las dos preguntas.
        $this->completarIntento([$seccion->id], respondidasPorSeccion: [
            $seccion->id => [$preguntas[0]->id],
        ]);

        $this->assertSame('disponible', $this->estadoEnPerfil()['trimestral']);
    }

    public function test_no_se_abre_un_intento_nuevo_si_hay_uno_en_progreso()
    {
        $this->asignarSecciones([$this->secciones['trimestral']->id]);

        $enProgreso = EvaluacionIntento::create([
            'evaluacion_id' => $this->evaluacion->id,
            'usuario_id' => $this->usuario->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => Carbon::now(),
            'estado' => EvaluacionIntento::ESTADO_EN_PROGRESO,
            'secciones_permitidas' => [$this->secciones['trimestral']->id],
        ]);

        $this->asignarSecciones([
            $this->secciones['trimestral']->id,
            $this->secciones['bimestral']->id,
        ]);

        $response = $this->actingAs($this->usuario)
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), ['proveedor_id' => $this->proveedor->id]);

        $response->assertRedirect(route('evaluaciones.intento', $enProgreso->id));
        $this->assertSame(1, EvaluacionIntento::where('usuario_id', $this->usuario->id)->count());
    }

    public function test_si_todo_esta_bloqueado_avisa_con_la_fecha_de_cada_subencueta()
    {
        $this->asignarSecciones([$this->secciones['trimestral']->id]);
        $this->completarIntento([$this->secciones['trimestral']->id], finalizadaEn: Carbon::now()->subDays(1));

        $response = $this->actingAs($this->usuario)
            ->from(route('evaluaciones.mis'))
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), ['proveedor_id' => $this->proveedor->id]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'Seccion trimestral',
            session('error'),
            'El mensaje debe nombrar la subencueta bloqueada'
        );
        $this->assertSame(1, EvaluacionIntento::where('usuario_id', $this->usuario->id)->count());
    }

    public function test_una_subencueta_sin_preguntas_no_genera_un_intento_vacio()
    {
        $this->asignarSecciones([$this->secciones['sin_preguntas']->id]);

        $response = $this->actingAs($this->usuario)
            ->from(route('evaluaciones.mis'))
            ->post(route('evaluaciones.iniciar', $this->evaluacion->id), ['proveedor_id' => $this->proveedor->id]);

        $this->assertSame(0, EvaluacionIntento::where('usuario_id', $this->usuario->id)->count());
        $response->assertSessionHas('error');
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

    private function asignarSecciones(array $seccionesIds)
    {
        $this->usuario->evaluaciones()->syncWithoutDetaching([
            $this->evaluacion->id => ['activo' => true, 'secciones' => json_encode(array_values($seccionesIds))],
        ]);
    }

    private function completarIntento(array $seccionesIds, $finalizadaEn = null, array $respondidasPorSeccion = [])
    {
        $intento = EvaluacionIntento::create([
            'evaluacion_id' => $this->evaluacion->id,
            'usuario_id' => $this->usuario->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_inicio' => ($finalizadaEn ? $finalizadaEn->copy() : Carbon::now()),
            'fecha_finalizacion' => $finalizadaEn ?: Carbon::now(),
            'estado' => EvaluacionIntento::ESTADO_COMPLETADO,
            'secciones_permitidas' => array_values($seccionesIds),
        ]);

        foreach ($seccionesIds as $seccionId) {
            $preguntaIds = array_key_exists($seccionId, $respondidasPorSeccion)
                ? $respondidasPorSeccion[$seccionId]
                : EvaluacionPregunta::where('seccion_id', $seccionId)->where('activo', true)->pluck('id')->all();

            foreach ($preguntaIds as $preguntaId) {
                $opcionId = EvaluacionOpcion::where('pregunta_id', $preguntaId)->value('id');
                EvaluacionRespuesta::create([
                    'intento_id' => $intento->id,
                    'pregunta_id' => $preguntaId,
                    'opcion_id' => $opcionId,
                    'puntos_obtenidos' => 5,
                ]);
            }
        }

        return $intento;
    }

    private function secciones_estadoEnPerfil()
    {
        $evaluaciones = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.mis'))
            ->viewData('evaluaciones');

        return $evaluaciones[0]->secciones_estado;
    }

    private function estadoEnPerfil()
    {
        $clavePorTitulo = [
            'Seccion trimestral' => 'trimestral',
            'Seccion bimestral' => 'bimestral',
            'Seccion semestral' => 'semestral',
            'Seccion sin preguntas' => 'sin_preguntas',
        ];

        $estado = [];
        foreach ($this->secciones_estadoEnPerfil() as $info) {
            $estado[$clavePorTitulo[$info['seccion']->titulo]] = $info['estado'];
        }

        return $estado;
    }

    private function tienePendientes()
    {
        $evaluaciones = $this->actingAs($this->usuario)
            ->get(route('evaluaciones.mis'))
            ->viewData('evaluaciones');

        return $evaluaciones[0]->tiene_pendientes;
    }

    private function limiteDe($clave)
    {
        foreach ($this->secciones_estadoEnPerfil() as $info) {
            if ($info['seccion']->titulo === 'Seccion ' . $clave) {
                return $info['estado'] === 'bloqueada' ? $info['fecha'] : null;
            }
        }

        return null;
    }
}
