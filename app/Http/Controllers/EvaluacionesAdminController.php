<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use App\Models\EvaluacionOpcion;
use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionProveedor;
use App\Models\EvaluacionSeccion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EvaluacionesAdminController extends Controller
{
    // ---------- EVALUACIONES ----------

    public function index()
    {
        $evaluaciones = Evaluacion::withCount('secciones', 'proveedores')->orderBy('titulo')->get();
        return view('admin.evaluaciones.index', compact('evaluaciones'));
    }

    public function create()
    {
        return view('admin.evaluaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
        ]);

        Evaluacion::create($request->only('titulo', 'descripcion', 'tipo_usuario') + ['activo' => $request->has('activo')]);

        return redirect()->route('admin.evaluaciones.index')->with('ok', 'Evaluación creada');
    }

    public function show($id)
    {
        $evaluacion = Evaluacion::with(['secciones.preguntas.opciones'])->findOrFail($id);
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $asociados = EvaluacionProveedor::where('evaluacion_id', $id)->where('activo', true)->pluck('proveedor_id')->toArray();
        $todasProveedores = Proveedor::orderBy('nombre')->get();

        return view('admin.evaluaciones.show', compact('evaluacion', 'proveedores', 'asociados', 'todasProveedores'));
    }

    public function update(Request $request, $id)
    {
        $evaluacion = Evaluacion::findOrFail($id);

        $request->validate([
            'titulo' => 'required',
        ]);

        $evaluacion->update($request->only('titulo', 'descripcion', 'tipo_usuario') + ['activo' => $request->has('activo')]);

        return back()->with('ok', 'Evaluación actualizada');
    }

    public function toggle($id)
    {
        $evaluacion = Evaluacion::findOrFail($id);
        $evaluacion->activo = !$evaluacion->activo;
        $evaluacion->save();

        return back();
    }

    public function destroy($id)
    {
        Evaluacion::findOrFail($id)->delete();

        return redirect()->route('admin.evaluaciones.index')->with('ok', 'Evaluación eliminada');
    }

    // ---------- SECCIONES ----------

    public function seccionStore(Request $request, $evaluacionId)
    {
        $request->validate([
            'titulo' => 'required',
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
        ]);

        EvaluacionSeccion::create([
            'evaluacion_id' => $evaluacionId,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'periodicidad' => $request->periodicidad ?: 'mensual',
            'orden' => $request->orden ?: 0,
        ]);

        return back()->with('ok', 'Sección agregada');
    }

    public function seccionUpdate(Request $request, $id)
    {
        $seccion = EvaluacionSeccion::findOrFail($id);

        $request->validate([
            'periodicidad' => 'nullable|in:mensual,bimestral,trimestral,semestral,anual',
        ]);

        $seccion->update([
            'titulo' => $request->titulo ?: $seccion->titulo,
            'descripcion' => $request->descripcion,
            'periodicidad' => $request->periodicidad ?: $seccion->periodicidad,
            'orden' => $request->orden ?: $seccion->orden,
            'activo' => $request->has('activo'),
        ]);

        return back()->with('ok', 'Sección actualizada');
    }

    public function seccionDestroy($id)
    {
        EvaluacionSeccion::findOrFail($id)->delete();

        return back()->with('ok', 'Sección eliminada');
    }

    // ---------- PREGUNTAS ----------

    public function preguntaStore(Request $request, $seccionId)
    {
        $request->validate(['pregunta' => 'required']);

        EvaluacionPregunta::create([
            'seccion_id' => $seccionId,
            'pregunta' => $request->pregunta,
            'puntos_maximos' => $request->puntos_maximos ?: 0,
            'orden' => $request->orden ?: 0,
        ]);

        return back()->with('ok', 'Pregunta agregada');
    }

    public function preguntaUpdate(Request $request, $id)
    {
        $pregunta = EvaluacionPregunta::findOrFail($id);
        $pregunta->update([
            'pregunta' => $request->pregunta ?: $pregunta->pregunta,
            'puntos_maximos' => $request->puntos_maximos ?: 0,
            'orden' => $request->orden ?: $pregunta->orden,
            'activo' => $request->has('activo'),
        ]);

        return back()->with('ok', 'Pregunta actualizada');
    }

    public function preguntaDestroy($id)
    {
        EvaluacionPregunta::findOrFail($id)->delete();

        return back()->with('ok', 'Pregunta eliminada');
    }

    // ---------- OPCIONES ----------

    public function opcionStore(Request $request, $preguntaId)
    {
        $request->validate(['texto' => 'required']);

        EvaluacionOpcion::create([
            'pregunta_id' => $preguntaId,
            'texto' => $request->texto,
            'puntos' => $request->puntos ?: 0,
            'valor' => $request->valor ?: 0,
            'orden' => $request->orden ?: 0,
        ]);

        return back()->with('ok', 'Opción agregada');
    }

    public function opcionUpdate(Request $request, $id)
    {
        $opcion = EvaluacionOpcion::findOrFail($id);
        $opcion->update([
            'texto' => $request->texto ?: $opcion->texto,
            'puntos' => $request->puntos !== null ? $request->puntos : $opcion->puntos,
            'valor' => $request->valor !== null ? $request->valor : $opcion->valor,
            'orden' => $request->orden ?: $opcion->orden,
            'activo' => $request->has('activo'),
        ]);

        return back()->with('ok', 'Opción actualizada');
    }

    public function opcionDestroy($id)
    {
        EvaluacionOpcion::findOrFail($id)->delete();

        return back()->with('ok', 'Opción eliminada');
    }

    // ---------- PROVEEDORES ASOCIADOS ----------

    public function proveedorAssociate(Request $request, $evaluacionId)
    {
        $request->validate(['proveedor_id' => 'required|exists:proveedores,id']);

        EvaluacionProveedor::updateOrCreate(
            ['evaluacion_id' => $evaluacionId, 'proveedor_id' => $request->proveedor_id],
            ['activo' => true]
        );

        return back()->with('ok', 'Proveedor asociado');
    }

    public function proveedorDissociate($evaluacionId, $proveedorId)
    {
        EvaluacionProveedor::where('evaluacion_id', $evaluacionId)
            ->where('proveedor_id', $proveedorId)
            ->delete();

        return back()->with('ok', 'Proveedor desasociado');
    }

    // ---------- RESULTADOS ----------

    public function resultadosProveedores(Request $request)
    {
        $mesesDisponibles = EvaluacionIntento::where('estado', EvaluacionIntento::ESTADO_COMPLETADO)
            ->whereNotNull('fecha_finalizacion')
            ->orderByDesc('fecha_finalizacion')
            ->get()
            ->pluck('fecha_finalizacion')
            ->map(fn($f) => $f->format('Y-m'))
            ->unique()
            ->values();

        $mesSeleccionado = $request->get('mes');
        if ($mesSeleccionado && !$mesesDisponibles->contains($mesSeleccionado)) {
            $mesSeleccionado = null;
        }

        $query = EvaluacionIntento::with(['evaluacion', 'proveedor', 'usuario'])
            ->where('estado', EvaluacionIntento::ESTADO_COMPLETADO);

        if ($mesSeleccionado) {
            $query->whereYear('fecha_finalizacion', Carbon::createFromFormat('Y-m', $mesSeleccionado)->year)
                ->whereMonth('fecha_finalizacion', Carbon::createFromFormat('Y-m', $mesSeleccionado)->month);
        }

        $intentos = $query->whereNotNull('fecha_finalizacion')->get();

        $porProveedor = $intentos
            ->groupBy('proveedor_id')
            ->map(function ($intentosDeProveedor) {
                $promedio = $intentosDeProveedor->avg('porcentaje');
                $porMes = $intentosDeProveedor
                    ->sortByDesc(fn($i) => $i->fecha_finalizacion)
                    ->groupBy(fn($i) => $i->fecha_finalizacion->format('Y-m'));
                return [
                    'proveedor' => $intentosDeProveedor->first()->proveedor,
                    'promedio' => round((float) $promedio, 2),
                    'por_mes' => $porMes,
                ];
            })
            ->sortBy(fn($p) => strtolower($p['proveedor']->nombre ?? ''))
            ->values();

        return view('admin.resultados.proveedores', compact('porProveedor', 'mesesDisponibles', 'mesSeleccionado'));
    }

    public function resultadoIntento($intentoId)
    {
        $intento = EvaluacionIntento::with([
            'evaluacion',
            'proveedor',
            'usuario',
            'respuestas.pregunta.opcionesActivas',
            'respuestas.opcion',
        ])->where('id', $intentoId)->where('estado', EvaluacionIntento::ESTADO_COMPLETADO)->firstOrFail();

        // Solo las subencuetas que el intento cubrio: las demas no se contestaron
        // y mostrarlas como 0 pts daria una lectura equivocada.
        $secciones = $intento->evaluacion->secciones()
            ->whereIn('id', $intento->seccionesPermitidasIds())
            ->with(['preguntasActivas'])
            ->get()
            ->map(function ($seccion) use ($intento) {
                $seccion->preguntas->map(function ($pregunta) use ($intento) {
                    $pregunta->respuesta = $intento->respuestas->firstWhere('pregunta_id', $pregunta->id);
                    return $pregunta;
                });
                return $seccion;
            });

        return view('admin.resultados.detalle', compact('intento', 'secciones'));
    }
}
