@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Gestionar evaluación')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-11">
            <a href="{{route('admin.evaluaciones')}}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left"></i> Volver</a>

            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif

            {{-- Datos de la evaluación --}}
            <div class="card border shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="fw-bold mb-0">{{$evaluacion->titulo}}</h3>
                            <span class="badge {{$evaluacion->activo ? 'bg-success' : 'bg-secondary'}} mt-1">{{$evaluacion->activo ? 'Activa' : 'Inactiva'}}</span>
                        </div>
                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editEval"><i class="fa fa-edit"></i> Editar</button>
                    </div>
                    <p class="text-muted mt-2">{{$evaluacion->descripcion}}</p>
                </div>
            </div>

            <div class="row g-4">


                {{-- Proveedores asociados --}}
                <div class="col-sm-12 col-lg-12">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Proveedores asociados</h5>

                            <form action="{{route('admin.evaluaciones.proveedores.associate', $evaluacion->id)}}" method="POST" class="mb-3">
                                @csrf
                                <div class="d-flex gap-2">
                                    <select name="proveedor_id" class="form-control form-control-sm" required>
                                        <option value="">Seleccionar proveedor</option>
                                        @foreach ($todasProveedores as $proveedor)
                                            @if (!in_array($proveedor->id, $asociados))
                                                <option value="{{$proveedor->id}}">{{$proveedor->nombre}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
                                </div>
                            </form>

                            <ul class="list-unstyled">
                                @forelse ($evaluacion->proveedores as $ep)
                                    @if ($ep->proveedor)
                                        <li class="d-flex justify-content-between align-items-center border-bottom py-2">
                                            <div>
                                                <strong>{{$ep->proveedor->nombre}}</strong><br>
                                                <small class="text-muted">{{$ep->proveedor->correo}}</small>
                                            </div>
                                            <form action="{{route('admin.evaluaciones.proveedores.dissociate', [$evaluacion->id, $ep->proveedor_id])}}" method="POST" onsubmit="return confirm('¿Quitar proveedor de esta evaluación?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-secondary"><i class="fa fa-times"></i></button>
                                            </form>
                                        </li>
                                    @endif
                                @empty
                                    <li class="text-muted">Sin proveedores asociados</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Columnas: secciones/preguntas/opciones --}}
                <div class="col-sm-12 col-lg-12">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Secciones, preguntas y opciones</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newSeccion"><i class="fa fa-plus"></i> Sección</button>
                            </div>

                            @forelse ($evaluacion->secciones as $seccion)
                                <div class="border rounded p-3 mb-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">{{$seccion->titulo}}
                                            <span class="badge bg-info text-dark ms-1"><i class="fa fa-calendar me-1"></i>{{$seccion->periodicidadLabel()}}</span>
                                            <span class="badge {{$seccion->activo ? 'bg-success' : 'bg-secondary'}} ms-1">{{$seccion->activo ? 'Activa' : 'Inactiva'}}</span>
                                        </h6>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editSeccion{{$seccion->id}}"><i class="fa fa-edit"></i></button>
                                            <form action="{{route('admin.secciones.destroy', $seccion->id)}}" method="POST" onsubmit="return confirm('¿Eliminar sección y sus preguntas?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-secondary btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    @if ($seccion->descripcion)
                                        <small class="text-muted">{{$seccion->descripcion}}</small>
                                    @endif

                                    <button class="btn btn-primary btn-sm mt-2 btn-add-pregunta" data-seccion="{{$seccion->id}}"><i class="fa fa-plus"></i> Pregunta</button>

                                    {{-- Preguntas --}}
                                    @forelse ($seccion->preguntas as $pregunta)
                                        <div class="border rounded p-2 mt-2 bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{$pregunta->pregunta}}</strong>
                                                    <span class="badge bg-warning text-dark ms-2">Máx: {{$pregunta->puntos_maximos}}</span>
                                                    <span class="badge {{$pregunta->activo ? 'bg-success' : 'bg-secondary'}}">{{$pregunta->activo ? 'Activa' : 'Inactiva'}}</span>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#editPregunta{{$pregunta->id}}"><i class="fa fa-edit"></i></button>
                                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newOpcion{{$pregunta->id}}"><i class="fa fa-plus"></i></button>
                                                    <form action="{{route('admin.preguntas.destroy', $pregunta->id)}}" method="POST" onsubmit="return confirm('¿Eliminar pregunta y sus opciones?');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-secondary btn-sm"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if ($pregunta->opciones->count())
                                                <ul class="list-unstyled mt-2 mb-0">
                                                    @foreach ($pregunta->opciones as $opcion)
                                                        <li class="d-flex justify-content-between align-items-center border-top py-1">
                                                            <span>{{$opcion->texto}}
                                                                <small class="text-muted">({{$opcion->puntos}} pts · valor {{$opcion->valor}})</small>
                                                                <span class="badge {{$opcion->activo ? 'bg-success' : 'bg-secondary'}}">{{$opcion->activo ? 'A' : 'I'}}</span>
                                                            </span>
                                                            <div class="d-flex gap-1">
                                                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editOpcion{{$opcion->id}}"><i class="fa fa-edit"></i></button>
                                                                <form action="{{route('admin.opciones.destroy', $opcion->id)}}" method="POST" onsubmit="return confirm('¿Eliminar opción?');">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-sm btn-secondary"><i class="fa fa-trash"></i></button>
                                                                </form>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <small class="text-muted d-block mt-2">Sin opciones. Agrega las 3 opciones de respuesta.</small>
                                            @endif
                                        </div>
                                    @empty
                                        <small class="text-muted d-block mt-2">Sin preguntas en esta sección.</small>
                                    @endforelse
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fa-solid fa-layer-group fa-3x mb-2"></i>
                                    <p>No hay secciones. Agrega la primera sección.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
</div>

{{-- Modal editar evaluación --}}
<div class="modal fade" id="editEval" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">Editar evaluación</div>
            <form action="{{route('admin.evaluaciones.update', $evaluacion->id)}}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Título</label>
                        <input type="text" name="titulo" value="{{$evaluacion->titulo}}" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control">{{$evaluacion->descripcion}}</textarea>
                    </div>
                    <div class="mb-2">
                        <label>Tipo de usuario</label>
                        <input type="text" name="tipo_usuario" value="{{$evaluacion->tipo_usuario}}" class="form-control">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" {{$evaluacion->activo ? 'checked' : ''}}>
                        <label class="form-check-label">Activa</label>
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

{{-- Modal nueva sección --}}
<div class="modal fade" id="newSeccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">Nueva sección</div>
            <form action="{{route('admin.secciones.store', $evaluacion->id)}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Título de la sección</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-2">
                        <label>Periodicidad</label>
                        <select name="periodicidad" class="form-control">
                            @foreach (['mensual','bimestral','trimestral','semestral','anual'] as $p)
                                <option value="{{$p}}">{{ucfirst($p)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Orden</label>
                        <input type="number" name="orden" value="0" class="form-control">
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

{{-- Modales por sección/pregunta/opción --}}
@foreach ($evaluacion->secciones as $seccion)
    {{-- Editar seccion --}}
    <div class="modal fade" id="editSeccion{{$seccion->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">Editar sección</div>
                <form action="{{route('admin.secciones.update', $seccion->id)}}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Título</label>
                            <input type="text" name="titulo" value="{{$seccion->titulo}}" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Descripción</label>
                            <textarea name="descripcion" rows="2" class="form-control">{{$seccion->descripcion}}</textarea>
                        </div>
                        <div class="mb-2">
                            <label>Periodicidad</label>
                            <select name="periodicidad" class="form-control">
                                @foreach (['mensual','bimestral','trimestral','semestral','anual'] as $p)
                                    <option value="{{$p}}" {{($seccion->periodicidad ?: 'mensual') == $p ? 'selected' : ''}}>{{ucfirst($p)}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Orden</label>
                            <input type="number" name="orden" value="{{$seccion->orden}}" class="form-control">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" {{$seccion->activo ? 'checked' : ''}}>
                            <label class="form-check-label">Activa</label>
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

    {{-- Nueva pregunta en seccion --}}
    <div class="modal fade" id="newPregunta{{$seccion->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">Nueva pregunta</div>
                <form action="{{route('admin.preguntas.store', $seccion->id)}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Pregunta</label>
                            <input type="text" name="pregunta" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Puntos máximos</label>
                            <input type="number" step="0.01" name="puntos_maximos" value="0" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Orden</label>
                            <input type="number" name="orden" value="0" class="form-control">
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

    @foreach ($seccion->preguntas as $pregunta)
        {{-- Editar pregunta --}}
        <div class="modal fade" id="editPregunta{{$pregunta->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">Editar pregunta</div>
                    <form action="{{route('admin.preguntas.update', $pregunta->id)}}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-body">
                            <div class="mb-2">
                                <label>Pregunta</label>
                                <input type="text" name="pregunta" value="{{$pregunta->pregunta}}" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Puntos máximos</label>
                                <input type="number" step="0.01" name="puntos_maximos" value="{{$pregunta->puntos_maximos}}" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label>Orden</label>
                                <input type="number" name="orden" value="{{$pregunta->orden}}" class="form-control">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" {{$pregunta->activo ? 'checked' : ''}}>
                                <label class="form-check-label">Activa</label>
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

        {{-- Nueva opcion en pregunta --}}
        <div class="modal fade" id="newOpcion{{$pregunta->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">Nueva opción de respuesta</div>
                    <form action="{{route('admin.opciones.store', $pregunta->id)}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-2">
                                <label>Texto (ej. Si cumple, Cumple parcialmente, No cumple)</label>
                                <input type="text" name="texto" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Puntos</label>
                                <input type="number" step="0.01" name="puntos" value="0" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label>Valor</label>
                                <input type="number" step="0.01" name="valor" value="0" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label>Orden</label>
                                <input type="number" name="orden" value="0" class="form-control">
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

        @foreach ($pregunta->opciones as $opcion)
            {{-- Editar opcion --}}
            <div class="modal fade" id="editOpcion{{$opcion->id}}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">Editar opción</div>
                        <form action="{{route('admin.opciones.update', $opcion->id)}}" method="POST">
                            @csrf @method('PATCH')
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label>Texto</label>
                                    <input type="text" name="texto" value="{{$opcion->texto}}" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label>Puntos</label>
                                    <input type="number" step="0.01" name="puntos" value="{{$opcion->puntos}}" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Valor</label>
                                    <input type="number" step="0.01" name="valor" value="{{$opcion->valor}}" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label>Orden</label>
                                    <input type="number" name="orden" value="{{$opcion->orden}}" class="form-control">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="activo" {{$opcion->activo ? 'checked' : ''}}>
                                    <label class="form-check-label">Activa</label>
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
        @endforeach
    @endforeach
@endforeach

<script>
    document.querySelectorAll('[id^="newPregunta"]').forEach(function(el){
        // No automatico; se usa el boton dedicado de cada seccion en el pie
    });
    // boton agregar pregunta por seccion
    document.querySelectorAll('.btn-add-pregunta').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = btn.getAttribute('data-seccion');
            var modal = document.getElementById('newPregunta'+id);
            if(modal){ var m = new bootstrap.Modal(modal); m.show(); }
        });
    });
</script>

@endsection
