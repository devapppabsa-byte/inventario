@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Proveedores')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
            <h2 class="fw-bold mb-4"><i class="fa fa-truck me-2"></i>Proveedores</h2>

            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif

            <div class="row g-4">
                {{-- Formulario registro --}}
                <div class="col-sm-12 col-lg-4">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold">Nuevo proveedor</h5>
                            <form action="{{route('admin.proveedores.store')}}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" class="form-control form-control-sm" required>
                                    {!!$errors->first('nombre', '<small class="text-danger"> :message </small>')!!}
                                </div>
                                <div class="mb-2">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="2" class="form-control form-control-sm"></textarea>
                                </div>
                                <div class="mb-2">
                                    <label>Contacto</label>
                                    <input type="text" name="contacto" class="form-control form-control-sm">
                                </div>
                                <div class="mb-2">
                                    <label>Correo</label>
                                    <input type="email" name="correo" class="form-control form-control-sm">
                                    {!!$errors->first('correo', '<small class="text-danger"> :message </small>')!!}
                                </div>
                                <div class="mb-2">
                                    <label>Teléfono</label>
                                    <input type="text" name="telefono" class="form-control form-control-sm">
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="activo" checked>
                                    <label class="form-check-label">Activo</label>
                                </div>
                                <button class="btn btn-success">Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Lista --}}
                <div class="col-sm-12 col-lg-8">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Lista de proveedores</h5>
                            <div class="tabla-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Contacto</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($proveedores as $proveedor)
                                            <tr>
                                                <td data-label="Nombre">{{$proveedor->nombre}}</td>
                                                <td data-label="Contacto">{{$proveedor->contacto}}</td>
                                                <td data-label="Correo">{{$proveedor->correo}}</td>
                                                <td data-label="Estado">
                                                    <span class="badge {{$proveedor->activo ? 'bg-success' : 'bg-secondary'}}">{{$proveedor->activo ? 'Activo' : 'Inactivo'}}</span>
                                                </td>
                                                <td class="sin-etiqueta text-end">
                                                    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editProv{{$proveedor->id}}"><i class="fa fa-edit"></i></button>
                                                    <form action="{{route('admin.proveedores.toggle', $proveedor->id)}}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <button class="btn btn-sm {{$proveedor->activo ? 'btn-warning' : 'btn-success'}}"><i class="fa {{$proveedor->activo ? 'fa-ban' : 'fa-check'}}"></i></button>
                                                    </form>
                                                    <form action="{{route('admin.proveedores.destroy', $proveedor->id)}}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar proveedor?');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-secondary btn-sm"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="sin-etiqueta text-center text-muted">Sin proveedores</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales de edicion --}}
@forelse ($proveedores as $proveedor)
    <div class="modal fade" id="editProv{{$proveedor->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">Editar proveedor</div>
                <form action="{{route('admin.proveedores.update', $proveedor->id)}}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="{{$proveedor->nombre}}" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Descripción</label>
                            <textarea name="descripcion" rows="2" class="form-control">{{$proveedor->descripcion}}</textarea>
                        </div>
                        <div class="mb-2">
                            <label>Contacto</label>
                            <input type="text" name="contacto" value="{{$proveedor->contacto}}" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Correo</label>
                            <input type="email" name="correo" value="{{$proveedor->correo}}" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" value="{{$proveedor->telefono}}" class="form-control">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" {{$proveedor->activo ? 'checked' : ''}}>
                            <label class="form-check-label">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@empty
@endforelse

@endsection
