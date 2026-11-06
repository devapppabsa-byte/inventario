@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Detalle de resultado')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-9">
            <a href="{{route('admin.resultados.proveedores')}}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left"></i> Volver a resultados</a>

            <div class="card border shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h3 class="fw-bold mb-0">{{$intento->evaluacion->titulo}}</h3>
                            <p class="mb-0"><i class="fa fa-truck me-1"></i><strong>{{$intento->proveedor->nombre}}</strong></p>
                            <small class="text-muted">
                                <i class="fa fa-user me-1"></i>{{$intento->usuario->name}} ·
                                Finalizado: {{$intento->fecha_finalizacion->format('d/m/Y H:i')}}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="display-5 fw-bold text-dark">{{$intento->porcentaje}}%</div>
                            <span>Puntuación: {{number_format($intento->puntuacion, 2)}} pts</span>
                        </div>
                    </div>

                    @if ($intento->comentario)
                        <hr>
                        <strong>Comentario del evaluador:</strong>
                        <p class="text-muted mb-0">{{$intento->comentario}}</p>
                    @endif
                </div>
            </div>

            @forelse ($secciones as $seccion)
                <div class="card border shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0"><i class="fa fa-layer-group me-2"></i>{{$seccion->titulo}}</h5>
                        @if ($seccion->descripcion)
                            <small class="text-muted">{{$seccion->descripcion}}</small>
                        @endif
                    </div>
                    <div class="card-body">
                        @forelse ($seccion->preguntas as $pregunta)
                            @php
                                $respuesta = $pregunta->respuesta;
                                $opcionElegida = $respuesta ? $respuesta->opcion : null;
                                $obtenido = (float) ($respuesta ? $respuesta->puntos_obtenidos : 0);
                                $maximo = (float) $pregunta->puntos_maximos;
                            @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{$pregunta->pregunta}}</strong>
                                    <span class="badge bg-secondary text-white">{{number_format($obtenido, 2)}} / {{number_format($maximo, 2)}} pts</span>
                                </div>
                                <div class="mt-1">
                                    @if ($opcionElegida)
                                        <span class="badge bg-dark text-white"><i class="fa fa-check me-1"></i>{{$opcionElegida->texto}} ({{$opcionElegida->puntos}} pts)</span>
                                    @else
                                        <span class="badge text-bg-light border">Sin respuesta</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <small class="text-muted">Sin preguntas en esta sección.</small>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <p>No hay preguntas para mostrar.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
