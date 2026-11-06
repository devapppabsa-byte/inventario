@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Nueva evaluación')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-6">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold mb-0">Nueva evaluación</h3>
                        <a href="{{route('admin.evaluaciones')}}" class="btn btn-outline-secondary btn-sm">Volver</a>
                    </div>

                    <form action="{{route('admin.evaluaciones.store')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Título principal</label>
                            <input type="text" name="titulo" value="{{old('titulo')}}" class="form-control" required>
                            {!!$errors->first('titulo', '<small class="text-danger"> :message </small>')!!}
                        </div>
                        <div class="mb-3">
                            <label>Descripción</label>
                            <textarea name="descripcion" rows="3" class="form-control">{{old('descripcion')}}</textarea>
                        </div>
                        <div class="mb-3">
                            <label>Tipo de usuario (opcional, informativo)</label>
                            <input type="text" name="tipo_usuario" value="{{old('tipo_usuario')}}" class="form-control" placeholder="ej. Jefes, Operadores">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="activo" checked>
                            <label class="form-check-label">Activa</label>
                        </div>
                        <button class="btn btn-success">Guardar evaluación</button>
                    </form>
                </div>
            </div>
            <p class="text-muted mt-3 small"><i class="fa fa-info-circle me-1"></i>Después de crear la evaluación podrás agregar secciones, preguntas, definir las 3 opciones con su puntaje y asociar proveedores.</p>
        </div>
    </div>
</div>

@endsection
