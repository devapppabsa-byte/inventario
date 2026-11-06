@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Agentes de Soporte')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
            <h2 class="fw-bold mb-4"><i class="fa fa-headset me-2"></i>Agentes de Soporte</h2>

            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif
            @if (session('error'))
                <h5 class="text-danger fw-bold">{{session('error')}}</h5>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-muted">
                Los agentes entran desde la misma pantalla de login eligiendo
                <b>Agente de Soporte</b>. Solo pueden ver y responder las solicitudes,
                no acceden al panel ni a las demás áreas del sistema.
            </p>

            <div class="row g-4">
                {{-- Formulario de alta --}}
                <div class="col-sm-12 col-lg-4">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold">Nuevo agente</h5>
                            <form action="{{route('admin.soporte.agentes.store')}}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="{{old('nombre')}}" class="form-control form-control-sm" required>
                                    {!!$errors->first('nombre', '<small class="text-danger"> :message </small>')!!}
                                </div>
                                <div class="mb-2">
                                    <label>Correo (con este inicia sesión)</label>
                                    <input type="email" name="email" value="{{old('email')}}" class="form-control form-control-sm" required>
                                    {!!$errors->first('email', '<small class="text-danger"> :message </small>')!!}
                                </div>
                                <div class="mb-2">
                                    <label>Contraseña</label>
                                    <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                                    {!!$errors->first('password', '<small class="text-danger"> :message </small>')!!}
                                    <small class="text-muted">Mínimo 8 caracteres.</small>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="activo" value="1" checked>
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
                            <h5 class="fw-bold mb-3">Agentes registrados ({{$agentes->total()}})</h5>

                            <form method="GET" action="{{route('admin.soporte.agentes')}}" class="row g-2 mb-3">
                                <div class="col-sm-9">
                                    <input type="search" name="busqueda" value="{{request('busqueda')}}"
                                           class="form-control form-control-sm"
                                           placeholder="Buscar por nombre o correo">
                                </div>
                                <div class="col-sm-3 d-flex gap-2">
                                    <button class="btn btn-dark btn-sm w-100">Buscar</button>
                                    @if (request('busqueda'))
                                        <a href="{{route('admin.soporte.agentes')}}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            <div class="table-responsive">
                                <div class="tabla-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Alta</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($agentes as $agente)
                                            <tr>
                                                <td data-label="Nombre">{{$agente->nombre}}</td>
                                                <td data-label="Correo">{{$agente->email}}</td>
                                                <td data-label="Alta">{{$agente->created_at->format('d/m/Y')}}</td>
                                                <td data-label="Estado">
                                                    <span class="badge {{$agente->activo ? 'bg-success' : 'bg-secondary'}}">{{$agente->activo ? 'Activo' : 'Inactivo'}}</span>
                                                </td>
                                                <td class="text-end text-nowrap sin-etiqueta">
                                                    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editAgente{{$agente->id}}" title="Editar">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <form action="{{route('admin.soporte.agentes.toggle', $agente->id)}}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <button class="btn btn-sm {{$agente->activo ? 'btn-warning' : 'btn-success'}}" title="{{$agente->activo ? 'Desactivar' : 'Activar'}}">
                                                            <i class="fa {{$agente->activo ? 'fa-ban' : 'fa-check'}}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{route('admin.soporte.agentes.destroy', $agente->id)}}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('¿Eliminar a {{$agente->nombre}}? Los comentarios que ya escribió en las solicitudes se conservarán.');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-secondary btn-sm" title="Eliminar">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="sin-etiqueta text-center text-muted" colspan="5">
                                                    @if (request('busqueda'))
                                                        Ningún agente coincide con la búsqueda.
                                                    @else
                                                        Todavía no hay agentes de soporte registrados.
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                </div>
                            </div>

                            <p class="text-muted small mb-0">
                                {{$agentes->firstItem()}}–{{$agentes->lastItem()}} de {{$agentes->total()}}
                            </p>
                            {!!$agentes->onEachSide(1)->links()!!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales de edición --}}
@forelse ($agentes as $agente)
    <div class="modal fade" id="editAgente{{$agente->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">Editar agente</div>
                <form action="{{route('admin.soporte.agentes.update', $agente->id)}}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="{{old('nombre', $agente->nombre)}}" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Correo</label>
                            <input type="email" name="email" value="{{old('email', $agente->email)}}" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Cambiar contraseña</label>
                            {{-- Se deja vacía a propósito: si trajera el hash, el
                                 guardado lo volvería a hashear y la clave dejaría
                                 de funcionar. Déjala en blanco para conservarla. --}}
                            <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
                            <small class="text-muted">Déjalo en blanco para mantener la contraseña actual.</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" value="1" {{$agente->activo ? 'checked' : ''}}>
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
