@extends('layout')
@section('title', 'Responder evaluación')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid mt-4" id="content">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <a href="{{route('evaluaciones.mis')}}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left"></i> Volver</a>

            @if (session('info'))
                <h5 class="text-info fw-bold">{{session('info')}}</h5>
            @endif
            @if (session('error'))
                <h5 class="text-danger fw-bold">{{session('error')}}</h5>
            @endif
            @if ($errors->any())
                <h5 class="text-danger fw-bold">{{$errors->first()}}</h5>
            @endif

            <div class="card border shadow-sm">
                <div class="card-body">
                    @if ($seccion)
                        <h3 class="fw-bold">{{$seccion->titulo}}</h3>
                        <span class="badge bg-info text-dark mb-2"><i class="fa fa-calendar me-1"></i>{{$seccion->periodicidadLabel()}}</span>
                        @if ($seccion->descripcion)
                            <p class="text-muted">{{$seccion->descripcion}}</p>
                        @endif
                        <p class="text-muted small">Encuesta: {{$evaluacion->titulo}}</p>
                    @else
                        <h3 class="fw-bold">{{$evaluacion->titulo}}</h3>
                        <p class="text-muted">{{$evaluacion->descripcion}}</p>
                    @endif

                    <hr>

                    @if ($tieneIntentoEnProgreso)
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle me-2"></i>Ya tienes un intento en progreso. Continúalo desde "Mis evaluaciones".
                        </div>
                        <a href="{{route('evaluaciones.mis')}}" class="btn btn-primary">Ir a mis evaluaciones</a>
                    @elseif ($proveedores->isEmpty())
                        <div class="alert alert-warning">Esta evaluación no tiene proveedores asociados todavía.</div>
                    @elseif ($seccionesDisponibles->isEmpty())
                        <div class="alert alert-secondary">
                            <i class="fa fa-lock me-2"></i>No tienes subencuetas pendientes en esta evaluación.
                            @if ($seccionesBloqueadas->isNotEmpty())
                                <hr class="my-2">
                                <small>Volverán a estar disponibles:</small>
                                <ul class="mb-0 mt-1">
                                    @foreach ($seccionesBloqueadas as $bloqueada)
                                        <li>{{$bloqueada->titulo}} — el {{$bloqueada->fecha_disponible->format('d/m/Y')}}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <a href="{{route('evaluaciones.mis')}}" class="btn btn-outline-secondary">Volver</a>
                    @else
                        <h5 class="fw-bold">Selecciona el proveedor a evaluar</h5>
                        @if (!$seccion)
                            <p class="text-muted small">Vas a contestar {{$seccionesDisponibles->count()}} subencuesta{{$seccionesDisponibles->count() === 1 ? '' : 's'}}:</p>
                            <ul class="small mb-3">
                                @foreach ($seccionesDisponibles as $pendiente)
                                    <li>{{$pendiente->titulo}} <span class="text-muted">({{$pendiente->periodicidadLabel()}})</span></li>
                                @endforeach
                            </ul>
                        @endif
                        <form action="{{route('evaluaciones.iniciar', $evaluacion->id)}}" method="POST">
                            @csrf
                            @if ($seccion)
                                <input type="hidden" name="seccion_id" value="{{$seccion->id}}">
                            @endif
                            <div class="mb-3">
                                <select name="proveedor_id" class="form-control" required>
                                    <option value="">Selecciona un proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{$proveedor->id}}">{{$proveedor->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-success"><i class="fa fa-play"></i> {{$seccion ? 'Iniciar subencueta' : 'Iniciar evaluación'}}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
