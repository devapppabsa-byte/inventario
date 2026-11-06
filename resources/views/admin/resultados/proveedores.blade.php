@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Resultados de evaluaciones')

@php
    // Con el guard: si la vista se renderiza dos veces en el mismo proceso
    // (tests, Octane, colas) PHP no vuelve a declarar la funcion y no revienta.
    if (!function_exists('formatoMes')) {
        function formatoMes($ym) {
            if (!$ym) return 'Sin fecha';
            try {
                return ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y'));
            } catch (\Exception $e) {
                return $ym;
            }
        }
    }
@endphp

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-11">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="fw-bold mb-0"><i class="fa fa-chart-bar me-2"></i>Resultados de evaluaciones</h2>

                <form method="GET" action="{{route('admin.resultados.proveedores')}}" class="d-flex align-items-center gap-2">
                    <label class="fw-bold mb-0">Mes:</label>
                    <select name="mes" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach ($mesesDisponibles as $mes)
                            <option value="{{$mes}}" {{$mesSeleccionado == $mes ? 'selected' : ''}}>{{formatoMes($mes)}}</option>
                        @endforeach
                    </select>
                    <a href="{{route('admin.resultados.proveedores')}}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-times"></i></a>
                </form>
            </div>

            @if ($mesSeleccionado)
                <div class="alert alert-secondary">Mostrando resultados del mes de <strong>{{formatoMes($mesSeleccionado)}}</strong></div>
            @endif

            @forelse ($porProveedor as $item)
                @php $proveedor = $item['proveedor']; @endphp
                <div class="card border shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa fa-truck me-2"></i>{{$proveedor->nombre}}</h5>
                        <span class="badge bg-dark text-white">Promedio global: {{$item['promedio']}}%</span>
                    </div>
                    <div class="card-body">
                        @forelse ($item['por_mes'] as $mes => $intentosMes)
                            <h6 class="fw-bold text-dark mt-2"><i class="fa fa-calendar me-1"></i>{{formatoMes($mes)}}</h6>
                            <div class="table-responsive">
                                <div class="tabla-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>Evaluación</th>
                                            <th>Evaluador</th>
                                            <th>Subencuesta</th>
                                            <th>Puntuación</th>
                                            <th>Porcentaje</th>
                                            <th>Comentario</th>
                                            <th>Fecha</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($intentosMes as $intento)
                                            <tr>
                                                <td data-label="Evaluación">{{$intento->evaluacion->titulo}}</td>
                                                <td data-label="Evaluador">{{$intento->usuario->name}}</td>
                                                <td data-label="Subencuesta">
                                                    @php
                                                        $titulos = \App\Models\EvaluacionSeccion::whereIn('id', $intento->seccionesPermitidasIds())->orderBy('orden')->pluck('titulo');
                                                    @endphp
                                                    @if ($titulos->count() === 1)
                                                        {{$titulos->first()}}
                                                    @elseif ($titulos->isEmpty())
                                                        <span class="text-muted">—</span>
                                                    @else
                                                        <span title="{{$titulos->implode(', ')}}">{{$titulos->count()}} subencuestas</span>
                                                    @endif
                                                </td>
                                                <td data-label="Puntuación">{{number_format($intento->puntuacion, 2)}} pts</td>
                                                <td data-label="Porcentaje"><span class="badge bg-secondary text-white">{{$intento->porcentaje}}%</span></td>
                                                <td data-label="Comentario">{{$intento->comentario ?: '—'}}</td>
                                                <td data-label="Fecha">{{$intento->fecha_finalizacion->format('d/m/Y')}}</td>
                                                <td class="sin-etiqueta">
                                                    <a href="{{route('admin.resultados.intento', $intento->id)}}" class="btn btn-dark btn-sm"><i class="fa fa-eye"></i> Detalle</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Sin evaluaciones completadas.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-chart-bar fa-4x mb-3"></i>
                    <h5>No hay resultados de evaluaciones completadas.</h5>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
