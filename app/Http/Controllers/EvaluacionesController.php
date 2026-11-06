<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use App\Models\EvaluacionRespuesta;
use App\Models\EvaluacionSeccion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluacionesController extends Controller
{
    public function misEvaluaciones()
    {
        $usuario = User::find(Auth::user()->id);
        $evaluaciones = Evaluacion::where('activo', true)
            ->whereIn('id', $usuario->evaluaciones()->pluck('evaluaciones.id'))
            ->with(['proveedores.proveedor', 'secciones'])
            ->get()
            ->filter(function ($evaluacion) {
                return count($this->seccionesUsuario($evaluacion->id)) > 0;
            })
            ->map(function ($evaluacion) {
                $seccionesPermitidas = $this->seccionesUsuario($evaluacion->id);
                $evaluacion->secciones_permitidas = $evaluacion->secciones->whereIn('id', $seccionesPermitidas)->values();

                $estado = $this->estadoSecciones($evaluacion->id, null, $seccionesPermitidas);
                $evaluacion->secciones_estado = $evaluacion->secciones_permitidas->map(function ($seccion) use ($estado) {
                    $seccionId = (int) $seccion->id;
                    $estadoSeccion = 'disponible';
                    $fecha = null;
                    if (in_array($seccionId, $estado['omitidas'], true)) {
                        $estadoSeccion = 'sin_preguntas';
                    } elseif (isset($estado['bloqueadas'][$seccionId])) {
                        $estadoSeccion = 'bloqueada';
                        $fecha = $estado['bloqueadas'][$seccionId];
                    } elseif (isset($estado['respondidas'][$seccionId])) {
                        $estadoSeccion = 'respondida';
                        $fecha = $estado['respondidas'][$seccionId];
                    }
                    return ['seccion' => $seccion, 'estado' => $estadoSeccion, 'fecha' => $fecha];
                })->values();
                $evaluacion->tiene_pendientes = count($estado['disponibles']) > 0;

                $seccionesPorId = $evaluacion->secciones->keyBy('id');
                $intentos = EvaluacionIntento::where('evaluacion_id', $evaluacion->id)
                    ->where('usuario_id', Auth::user()->id)
                    ->orderBy('id', 'desc')
                    ->get();
                $completadasPorIntento = $this->mapSeccionesCompletadas($intentos->pluck('id')->all());

                $evaluacion->intentos_usuario = $intentos->map(function ($intento) use ($completadasPorIntento, $seccionesPorId) {
                    $intento->fecha_disponible = null;
                    $intento->vigente = false;
                    if ($intento->estaCompletado() && $intento->fecha_finalizacion) {
                        $limites = collect($completadasPorIntento[$intento->id] ?? [])
                            ->map(function ($seccionId) use ($intento, $seccionesPorId) {
                                $seccion = $seccionesPorId->get((int) $seccionId);
                                return $seccion ? $intento->fecha_finalizacion->copy()->addDays($seccion->periodoDias()) : null;
                            })
                            ->filter()
                            ->sort();

                        if ($limites->isNotEmpty()) {
                            $disponible = $limites->first();
                            $intento->fecha_disponible = $disponible;
                            $intento->vigente = Carbon::now()->lt($disponible);
                        }
                    }
                    return $intento;
                });
                return $evaluacion;
            })
            ->values();

        return view('user.evaluaciones.index', compact('evaluaciones'));
    }

    private function seccionesUsuario($evaluacionId)
    {
        $pivote = Auth::user()->evaluaciones()->where('evaluaciones.id', $evaluacionId)->first();
        if (!$pivote) return [];
        $secciones = $pivote->pivot->secciones;
        if (is_string($secciones) && $secciones !== '') {
            $secciones = json_decode($secciones, true);
        }
        return is_array($secciones) ? array_values(array_map('intval', $secciones)) : [];
    }

    private function mapSeccionesCompletadas($intentoIds)
    {
        $intentoIds = array_values(array_map('intval', $intentoIds));
        if (count($intentoIds) === 0) {
            return [];
        }

        $seccionesPorIntento = EvaluacionIntento::with('evaluacion')
            ->whereIn('id', $intentoIds)
            ->get()
            ->mapWithKeys(function ($intento) {
                return [$intento->id => $intento->seccionesPermitidasIds()];
            });

        $contestadas = EvaluacionRespuesta::whereIn('intento_id', $intentoIds)
            ->whereNotNull('opcion_id')
            ->join('evaluacion_preguntas', 'evaluacion_preguntas.id', '=', 'evaluacion_respuestas.pregunta_id')
            ->where('evaluacion_preguntas.activo', true)
            ->groupBy('evaluacion_respuestas.intento_id', 'evaluacion_preguntas.seccion_id')
            ->selectRaw('evaluacion_respuestas.intento_id as intento_id, evaluacion_preguntas.seccion_id as seccion_id, count(*) as total')
            ->get()
            ->groupBy('intento_id')
            ->map(fn($filas) => $filas->mapWithKeys(fn($fila) => [(int) $fila->seccion_id => (int) $fila->total])->all());

        $todosLosIds = $seccionesPorIntento->flatten()->map(fn($id) => (int) $id)->unique()->all();
        $totales = EvaluacionSeccion::whereIn('id', $todosLosIds)
            ->withCount(['preguntas as preguntas_activas' => fn($q) => $q->where('activo', true)])
            ->get()
            ->keyBy('id');

        $resultado = [];
        foreach ($seccionesPorIntento as $intentoId => $seccionesIds) {
            $resultado[$intentoId] = [];
            if (count($seccionesIds) === 0) {
                continue;
            }

            $porSeccion = $contestadas->get($intentoId, []);

            foreach ($seccionesIds as $seccionId) {
                $seccion = $totales->get((int) $seccionId);
                if (!$seccion) {
                    continue;
                }
                $total = (int) $seccion->preguntas_activas;
                if ($total === 0) {
                    continue;
                }
                if ((int) ($porSeccion[(int) $seccionId] ?? 0) >= $total) {
                    $resultado[$intentoId][] = (int) $seccionId;
                }
            }
        }

        return $resultado;
    }

    private function estadoSecciones($evaluacionId, $proveedorId, $seccionesIds)
    {
        $estado = ['respondidas' => [], 'disponibles' => [], 'bloqueadas' => [], 'omitidas' => []];

        $seccionesIds = array_values(array_unique(array_map('intval', (array) $seccionesIds)));
        if (count($seccionesIds) === 0) {
            return $estado;
        }

        $intentos = EvaluacionIntento::where('evaluacion_id', $evaluacionId)
            ->where('usuario_id', Auth::user()->id)
            ->where('estado', EvaluacionIntento::ESTADO_COMPLETADO)
            ->whereNotNull('fecha_finalizacion')
            ->when($proveedorId, fn($q) => $q->where('proveedor_id', $proveedorId))
            ->orderBy('fecha_finalizacion', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'fecha_finalizacion']);

        $ultimaPorSeccion = [];
        if ($intentos->isNotEmpty()) {
            $completadasPorIntento = $this->mapSeccionesCompletadas($intentos->pluck('id')->all());
            foreach ($intentos as $intento) {
                foreach ($completadasPorIntento[$intento->id] ?? [] as $seccionId) {
                    if (!isset($ultimaPorSeccion[$seccionId])) {
                        $ultimaPorSeccion[$seccionId] = $intento->fecha_finalizacion;
                    }
                }
            }
        }

        $secciones = EvaluacionSeccion::whereIn('id', $seccionesIds)
            ->withCount(['preguntas as preguntas_activas' => fn($q) => $q->where('activo', true)])
            ->get()
            ->keyBy('id');

        foreach ($seccionesIds as $seccionId) {
            $seccion = $secciones->get($seccionId);
            if (!$seccion) {
                continue;
            }
            if ((int) $seccion->preguntas_activas === 0) {
                $estado['omitidas'][] = $seccionId;
                continue;
            }

            $ultima = $ultimaPorSeccion[$seccionId] ?? null;
            if ($ultima) {
                $estado['respondidas'][$seccionId] = $ultima;
                $limite = $ultima->copy()->addDays($seccion->periodoDias());
                if (Carbon::now()->lt($limite)) {
                    $estado['bloqueadas'][$seccionId] = $limite;
                    continue;
                }
            }

            $estado['disponibles'][] = $seccionId;
        }

        return $estado;
    }

    public function responder($evaluacionId)
    {
        $evaluacion = Evaluacion::where('id', $evaluacionId)->where('activo', true)->with('proveedores.proveedor')->firstOrFail();

        $seccionesPermitidas = $this->seccionesUsuario($evaluacionId);
        if (count($seccionesPermitidas) === 0) {
            return redirect()->route('evaluaciones.mis')->with('error', 'No tienes acceso a esta evaluación');
        }

        $estado = $this->estadoSecciones($evaluacionId, null, $seccionesPermitidas);
        $seccionesDisponibles = $this->seccionesTitulos($estado['disponibles']);
        $seccionesBloqueadas = $this->seccionesTitulosConFecha($evaluacionId, $estado['bloqueadas']);

        $tieneIntentoEnProgreso = EvaluacionIntento::where('evaluacion_id', $evaluacionId)
            ->where('usuario_id', Auth::user()->id)
            ->where('estado', EvaluacionIntento::ESTADO_EN_PROGRESO)
            ->exists();

        return view('user.evaluaciones.responder', [
            'evaluacion' => $evaluacion,
            'proveedores' => $evaluacion->proveedores->filter(fn($p) => $p->activo)->map(fn($p) => $p->proveedor),
            'tieneIntentoEnProgreso' => $tieneIntentoEnProgreso,
            'seccion' => null,
            'seccionesDisponibles' => $seccionesDisponibles,
            'seccionesBloqueadas' => $seccionesBloqueadas,
        ]);
    }

    /**
     * Entrada por subencueta: al hacer clic en una se abre solo esa.
     */
    public function responderSeccion($evaluacionId, $seccionId)
    {
        $evaluacion = Evaluacion::where('id', $evaluacionId)->where('activo', true)->with('proveedores.proveedor')->firstOrFail();

        $seccionId = (int) $seccionId;
        $seccion = EvaluacionSeccion::where('id', $seccionId)
            ->where('evaluacion_id', $evaluacion->id)
            ->where('activo', true)
            ->first();

        if (!$seccion) {
            return redirect()->route('evaluaciones.mis')->with('error', 'La subencueta ya no está disponible');
        }

        if (!in_array($seccionId, $this->seccionesUsuario($evaluacionId), true)) {
            return redirect()->route('evaluaciones.mis')->with('error', 'No tienes asignada la subencueta "' . $seccion->titulo . '"');
        }

        $estado = $this->estadoSecciones($evaluacionId, null, [$seccionId]);
        if (!in_array($seccionId, $estado['disponibles'], true)) {
            $motivo = 'La subencueta "' . $seccion->titulo . '" no se puede responder ahora mismo.';
            if (isset($estado['bloqueadas'][$seccionId])) {
                $motivo .= ' La podras responder de nuevo el ' . $estado['bloqueadas'][$seccionId]->format('d/m/Y') . '.';
            } elseif (in_array($seccionId, $estado['omitidas'], true)) {
                $motivo .= ' No tiene preguntas activas.';
            }
            return redirect()->route('evaluaciones.mis')->with('error', $motivo);
        }

        $tieneIntentoEnProgreso = EvaluacionIntento::where('evaluacion_id', $evaluacionId)
            ->where('usuario_id', Auth::user()->id)
            ->where('estado', EvaluacionIntento::ESTADO_EN_PROGRESO)
            ->exists();

        return view('user.evaluaciones.responder', [
            'evaluacion' => $evaluacion,
            'proveedores' => $evaluacion->proveedores->filter(fn($p) => $p->activo)->map(fn($p) => $p->proveedor),
            'tieneIntentoEnProgreso' => $tieneIntentoEnProgreso,
            'seccion' => $seccion,
            'seccionesDisponibles' => collect([$seccion]),
            'seccionesBloqueadas' => collect(),
        ]);
    }

    private function seccionesTitulos($seccionesIds)
    {
        if (count($seccionesIds) === 0) {
            return collect();
        }
        return EvaluacionSeccion::whereIn('id', $seccionesIds)->orderBy('orden')->get();
    }

    private function seccionesTitulosConFecha($evaluacionId, $bloqueadas)
    {
        if (count($bloqueadas) === 0) {
            return collect();
        }
        $secciones = EvaluacionSeccion::whereIn('id', array_keys($bloqueadas))->orderBy('orden')->get();
        return $secciones->map(function ($seccion) use ($bloqueadas) {
            $seccion->fecha_disponible = $bloqueadas[$seccion->id];
            return $seccion;
        });
    }

    public function iniciar(Request $request, $evaluacionId)
    {
        $evaluacion = Evaluacion::where('id', $evaluacionId)->where('activo', true)->firstOrFail();

        $request->validate(['proveedor_id' => 'required']);

        $seccionesPermitidas = $this->seccionesUsuario($evaluacionId);
        if (count($seccionesPermitidas) === 0) {
            return back()->with('error', 'No tienes acceso a esta evaluación');
        }

        $proveedorAsociado = $evaluacion->proveedores()->where('proveedor_id', $request->proveedor_id)->where('activo', true)->exists();
        if (!$proveedorAsociado) {
            return back()->withErrors(['proveedor_id' => 'El proveedor no está asociado a esta evaluación']);
        }

        $activo = EvaluacionIntento::where('evaluacion_id', $evaluacionId)
            ->where('usuario_id', Auth::user()->id)
            ->where('estado', EvaluacionIntento::ESTADO_EN_PROGRESO)
            ->first();

        if ($activo) {
            return redirect()->route('evaluaciones.intento', $activo->id)->with('info', 'Ya tienes un intento en progreso');
        }

        // Si viene una subencueta concreta, el intento se crea solo con esa.
        $seccionId = $request->filled('seccion_id') ? (int) $request->input('seccion_id') : null;

        // Periodicidad por subencueta: solo se bloquean las subencuetas que el usuario
        // completo y que siguen dentro de su propio periodo. Las nuevas asignadas
        // quedan disponibles de inmediato.
        $estado = $this->estadoSecciones($evaluacionId, $request->proveedor_id, $seccionesPermitidas);
        $seccionesDisponibles = $estado['disponibles'];

        if ($seccionId !== null) {
            if (!in_array($seccionId, $seccionesPermitidas, true)) {
                return back()->with('error', 'No tienes asignada esa subencueta');
            }
            if (!in_array($seccionId, $seccionesDisponibles, true)) {
                $titulo = EvaluacionSeccion::where('id', $seccionId)->value('titulo') ?? 'La subencueta';
                if (isset($estado['bloqueadas'][$seccionId])) {
                    return back()->with('error', 'Ya respondiste "' . $titulo . '". La podras volver a contestar el '
                        . $estado['bloqueadas'][$seccionId]->format('d/m/Y') . '.');
                }
                if (in_array($seccionId, $estado['omitidas'], true)) {
                    return back()->with('error', '"' . $titulo . '" no tiene preguntas activas');
                }
                return back()->with('error', 'No puedes responder "' . $titulo . '" ahora mismo');
            }
            $seccionesDisponibles = [$seccionId];
        }

        if (count($seccionesDisponibles) === 0) {
            if (count($estado['bloqueadas']) === 0) {
                return back()->with('error', 'No tienes subencuetas pendientes en esta evaluación');
            }

            $titulos = EvaluacionSeccion::whereIn('id', array_keys($estado['bloqueadas']))->get()->keyBy('id');
            $detalle = collect($estado['bloqueadas'])->map(function ($limite, $seccionId) use ($titulos) {
                $titulo = $titulos->get($seccionId)->titulo ?? 'Subencuesta ' . $seccionId;
                return $titulo . ' (disponible el ' . $limite->format('d/m/Y') . ')';
            })->implode(', ');


            return back()->with('error', 'Ya respondiste estas subencuetas: ' . $detalle);
        }

        $intento = EvaluacionIntento::create([
            'evaluacion_id' => $evaluacionId,
            'usuario_id' => Auth::user()->id,
            'proveedor_id' => $request->proveedor_id,
            'fecha_inicio' => Carbon::now(),
            'estado' => EvaluacionIntento::ESTADO_EN_PROGRESO,
            'secciones_permitidas' => $seccionesDisponibles,
        ]);

        return redirect()->route('evaluaciones.intento', $intento->id);
    }

    public function intento($intentoId)
    {
        $intento = EvaluacionIntento::with('evaluacion', 'proveedor', 'respuestas')
            ->where('id', $intentoId)
            ->where('usuario_id', Auth::user()->id)
            ->firstOrFail();

        // Cancelar si venció
        if ($intento->esVencido()) {
            $intento->estado = EvaluacionIntento::ESTADO_CANCELADO;
            $intento->fecha_finalizacion = Carbon::now();
            $intento->save();
            return redirect()->route('evaluaciones.mis')->with('info', 'El intento venció y fue cancelado');
        }

        if ($intento->estaCompletado()) {
            return redirect()->route('evaluaciones.resultado', $intento->id);
        }

        $seccionIds = $intento->seccionesPermitidasIds();
        $secciones = $intento->evaluacion->seccionesActivas()->whereIn('id', $seccionIds)->with(['preguntasActivas' => function ($q) {
            $q->where('activo', true)->with(['opcionesActivas' => function ($q2) {
                $q2->where('activo', true);
            }]);
        }])->get();

        $respuestas = $intento->respuestas->keyBy('pregunta_id');

        return view('user.evaluaciones.intento', compact('intento', 'secciones', 'respuestas'));
    }

    public function guardar(Request $request, $intentoId)
    {
        $intento = EvaluacionIntento::where('id', $intentoId)
            ->where('usuario_id', Auth::user()->id)
            ->where('estado', EvaluacionIntento::ESTADO_EN_PROGRESO)
            ->firstOrFail();

        $this->guardarRespuestas($request, $intento);

        $action = $request->input('action', 'guardar');

        if ($action === 'finalizar') {
            $intento->fecha_finalizacion = Carbon::now();
            $intento->comentario = $request->comentario ?: null;
        }

        $intento->recalcular();
        $intento->save();

        if ($action === 'finalizar') {
            $intento->estado = EvaluacionIntento::ESTADO_COMPLETADO;
            $intento->save();
            return redirect()->route('evaluaciones.resultado', $intento->id)->with('ok', 'Evaluación finalizada');
        }

        return back()->with('ok', 'Progreso guardado');
    }

    public function resultado($intentoId)
    {
        $intento = EvaluacionIntento::with('evaluacion', 'proveedor', 'respuestas')
            ->where('id', $intentoId)
            ->where('usuario_id', Auth::user()->id)
            ->firstOrFail();

        return view('user.evaluaciones.resultado', compact('intento'));
    }

    private function guardarRespuestas(Request $request, EvaluacionIntento $intento)
    {
        $preguntas = $intento->responderPreguntas();

        foreach ($preguntas as $pregunta) {
            $opcionId = $request->input('pregunta_' . $pregunta->id);
            $puntos = 0;

            if ($opcionId) {
                $opcion = $pregunta->opciones()->where('activo', true)->find($opcionId);
                $puntos = $opcion ? (float) $opcion->puntos : 0;
            }

            EvaluacionRespuesta::updateOrCreate(
                ['intento_id' => $intento->id, 'pregunta_id' => $pregunta->id],
                ['opcion_id' => $opcionId ?: null, 'puntos_obtenidos' => $puntos]
            );
        }
    }
}
