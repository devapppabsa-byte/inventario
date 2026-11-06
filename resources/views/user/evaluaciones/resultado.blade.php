@extends('layout')
@section('title', 'Resultado de evaluación')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid mt-4" id="content">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <a href="{{route('evaluaciones.mis')}}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left"></i> Volver</a>

            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif

            <div class="card border shadow-sm">
                <div class="card-body text-center">
                    <h3 class="fw-bold">{{$intento->evaluacion->titulo}}</h3>
                    <p class="mb-0"><i class="fa fa-truck me-1"></i>{{$intento->proveedor->nombre}}</p>
                    <small class="text-muted">{{$intento->fecha_finalizacion ? 'Finalizado: '.$intento->fecha_finalizacion->format('d/m/Y H:i') : ''}}</small>

                    <hr>

                    <div class="display-4 fw-bold text-primary"> {{$intento->porcentaje}}%</div>
                    <h2 class="fw-bold">Puntuación: {{$intento->puntuacion}} pts</h2>

                    @if ($intento->estaCompletado())
                        <span class="badge bg-success badge-lg">Completado</span>
                    @elseif ($intento->estaCancelado())
                        <span class="badge bg-secondary badge-lg">Cancelado</span>
                    @endif

                    @if ($intento->comentario)
                        <hr>
                        <div class="text-start">
                            <strong>Comentario:</strong>
                            <p class="text-muted">{{$intento->comentario}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
