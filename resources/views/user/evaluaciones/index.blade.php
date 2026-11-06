@extends('layout')
@section('title', 'Mis evaluaciones')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid mt-4" id="content">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <h2 class="fw-bold mb-3"><i class="fa-solid fa-clipboard-list me-2"></i>Mis evaluaciones de proveedores</h2>

            @if (session('info'))
                <h5 class="text-info fw-bold">{{session('info')}}</h5>
            @endif
            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif
            @if (session('error'))
                <h5 class="text-danger fw-bold">{{session('error')}}</h5>
            @endif

            @forelse ($evaluaciones as $evaluacion)
                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-0">{{$evaluacion->titulo}}</h5>
                                @if ($evaluacion->descripcion)
                                    <p class="text-muted mt-2 mb-0">{{$evaluacion->descripcion}}</p>
                                @endif
                                @if ($evaluacion->secciones_estado && $evaluacion->secciones_estado->count() > 0)
                                    <div class="list-group mt-3">
                                        @foreach ($evaluacion->secciones_estado as $info)
                                            @php
                                                $seccion = $info['seccion'];
                                                $estado = $info['estado'];
                                                $icono = 'fa-circle';
                                                $claseFila = 'list-group-item d-flex justify-content-between align-items-center gap-2';
                                                $estadoTexto = 'Disponible';
                                                $estadoClase = 'text-success';

                                                if ($estado === 'respondida') {
                                                    $icono = 'fa-check-circle';
                                                    $estadoTexto = 'Respondida el ' . $info['fecha']->format('d/m/Y');
                                                    $estadoClase = 'text-secondary';
                                                } elseif ($estado === 'bloqueada') {
                                                    $icono = 'fa-lock';
                                                    $estadoTexto = 'Disponible el ' . $info['fecha']->format('d/m/Y');
                                                    $estadoClase = 'text-secondary';
                                                } elseif ($estado === 'sin_preguntas') {
                                                    $icono = 'fa-circle-question';
                                                    $estadoTexto = 'Sin preguntas activas';
                                                    $estadoClase = 'text-muted';
                                                }
                                            @endphp
                                            @if ($estado === 'disponible')
                                                <a href="{{route('evaluaciones.responderSeccion', [$evaluacion->id, $seccion->id])}}"
                                                   class="{{$claseFila}} list-group-item-action list-group-item-success">
                                                    <span class="d-flex align-items-center gap-2">
                                                        <i class="fa {{$icono}} {{$estadoClase}}"></i>
                                                        <span>
                                                            <span class="d-block fw-semibold">{{$seccion->titulo}}</span>
                                                            <small class="text-muted">Click para contestarla eligiendo proveedor</small>
                                                        </span>
                                                    </span>
                                                    <span class="text-end text-nowrap">
                                                        <span class="badge bg-light text-dark border">{{$seccion->periodicidadLabel()}}</span>
                                                        <i class="fa fa-chevron-right ms-2 text-muted"></i>
                                                    </span>
                                                </a>
                                            @else
                                                <div class="{{$claseFila}} bg-light opacity-75">
                                                    <span class="d-flex align-items-center gap-2">
                                                        <i class="fa {{$icono}} {{$estadoClase}}"></i>
                                                        <span>
                                                            <span class="d-block">{{$seccion->titulo}}</span>
                                                            <small class="{{$estadoClase}}">{{$estadoTexto}}</small>
                                                        </span>
                                                    </span>
                                                    <span class="text-end text-nowrap">
                                                        <span class="badge bg-secondary text-white">{{$seccion->periodicidadLabel()}}</span>
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if ($evaluacion->tiene_pendientes)
                                {{-- <div class="text-end text-nowrap">
                                    <a href="{{route('evaluaciones.responder', $evaluacion->id)}}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-play"></i> Responder todo lo pendiente
                                    </a>
                                </div> --}}
                            @else
                                <div class="text-end">
                                    <button class="btn btn-light btn-sm border text-muted" disabled>
                                        <i class="fa fa-check-double"></i> Sin pendientes
                                    </button>
                                    <small class="d-block text-muted mt-1">
                                        @php $proxima = collect($evaluacion->secciones_estado)->where('estado', 'bloqueada')->pluck('fecha')->filter()->sort()->first(); @endphp
                                        @if ($proxima)
                                            Próxima Evaluación:  <strong>{{$proxima->format('d/m/Y')}}</strong>
                                        @else
                                            No tienes subencuetas con preguntas activas
                                        @endif
                                    </small>
                                </div>
                            @endif
                        </div>

                        <hr>
                        <h6 class="fw-bold">Historial</h6>
                        @forelse ($evaluacion->intentos_usuario as $intento)
                            @if ($intento->proveedor)
                                <div class="border-bottom py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{$intento->proveedor->nombre}}</strong>
                                            @php
                                                $titulos = \App\Models\EvaluacionSeccion::whereIn('id', $intento->seccionesPermitidasIds())
                                                    ->orderBy('orden')->pluck('titulo');
                                            @endphp
                                            @if ($titulos->count() === 1)
                                                <span class="ms-2 badge bg-light text-dark border">{{$titulos->first()}}</span>
                                            @elseif ($titulos->count() > 1)
                                                <span class="ms-2 badge bg-light text-dark border" title="{{$titulos->implode(', ')}}">
                                                    {{$titulos->count()}} subencuestas
                                                </span>
                                            @endif
                                            <span class="ms-2 badge
                                                {{$intento->estaCompletado() ? 'bg-success' : ($intento->estaCancelado() ? 'bg-secondary' : 'bg-warning text-dark')}}">
                                                {{ucfirst($intento->estado)}}
                                            </span>
                                            @if ($intento->estaCompletado())
                                                <span class="ms-2"><i class="fa fa-percent"></i> {{$intento->porcentaje}}%</span>
                                            @endif
                                        </div>
                                        @if ($intento->estaEnProgreso())
                                            <a href="{{route('evaluaciones.intento', $intento->id)}}" class="btn btn-warning btn-sm">Continuar</a>
                                        @elseif ($intento->estaCompletado())
                                            <a href="{{route('evaluaciones.resultado', $intento->id)}}" class="btn btn-outline-secondary btn-sm">Ver</a>
                                        @endif
                                    </div>
                                    @if ($intento->vigente && $intento->fecha_disponible)
                                        <div class="alert alert-secondary small mb-0 mt-2 py-1">
                                            <i class="fa fa-check-circle me-1"></i>Completaste las subencuetas de <strong>{{$intento->proveedor->nombre}}</strong> el {{$intento->fecha_finalizacion->format('d/m/Y')}}. 
                                            <br> <i class="fa fa-calendar"></i> La proxima fecha en la que debes contestar es: <strong>{{$intento->fecha_disponible->format('d/m/Y')}}</strong>.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @empty
                            <small class="text-muted">Aún no has contestado esta evaluación.</small>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-clipboard-list fa-4x mb-3"></i>
                    <h5>No tienes evaluaciones asignadas</h5>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
