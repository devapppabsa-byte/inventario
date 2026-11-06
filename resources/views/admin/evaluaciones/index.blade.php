@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Evaluaciones')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0"><i class="fa fa-clipboard-list me-2"></i>Evaluaciones</h2>
                <a href="{{route('admin.evaluaciones.create')}}" class="btn btn-success"><i class="fa fa-plus me-1"></i>Nueva evaluación</a>
            </div>

            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif

            <div class="row g-3">
                @forelse ($evaluaciones as $evaluacion)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="fw-bold mb-0">{{$evaluacion->titulo}}</h5>
                                    <span class="badge {{$evaluacion->activo ? 'bg-success' : 'bg-secondary'}}">{{$evaluacion->activo ? 'Activo' : 'Inactivo'}}</span>
                                </div>
                                <p class="text-muted mb-2"><i class="fa fa-layer-group me-1"></i>{{$evaluacion->secciones_count}} secciones · {{$evaluacion->proveedores_count}} proveedores</p>
                                @if ($evaluacion->descripcion)
                                    <small class="text-muted">{{$evaluacion->descripcion}}</small>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center">
                                <a href="{{route('admin.evaluaciones.show', $evaluacion->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Gestionar</a>
                                <div class="d-flex gap-1">
                                    <form action="{{route('admin.evaluaciones.toggle', $evaluacion->id)}}" method="POST">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{$evaluacion->activo ? 'btn-warning' : 'btn-success'}}"><i class="fa {{$evaluacion->activo ? 'fa-ban' : 'fa-check'}}"></i></button>
                                    </form>
                                    <form action="{{route('admin.evaluaciones.destroy', $evaluacion->id)}}" method="POST" onsubmit="return confirm('¿Eliminar evaluación (incluye secciones, preguntas y respuestas)?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-secondary"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fa-solid fa-clipboard-list fa-4x mb-3"></i>
                        <h5>No hay evaluaciones creadas</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
