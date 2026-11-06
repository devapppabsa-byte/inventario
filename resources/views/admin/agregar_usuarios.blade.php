
@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Agregar usuarios')



<div class="container-fluid mt-5">
    
    <div class="row justify-content-center">
        {{-- div que encierra el formulario del usuario --}}
        <div class="col-sm-10 col-md-4  col-lg-2 bg-white p-4 mx-1 shadow-sm mb-3">
                <form action="{{route('registrar.usuarios')}}" method="POST" >
                    @csrf
                <div class="row">
                    <div class="col-12">
                        <h4 class="fw-bold">Agregar usuario</h4>
                        @if (session('agregado'))
                            <span class="text-success fw-bold">{{session('agregado')}}</span>
                        @endif
                    </div>
                    
                    <div class="col-12 mt-3">
                        <div class="mb-3">
                            <label for="">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" value="{{old('nombre')}}">
                            {!!$errors->first('nombre', '<small class="text-danger"> :message </small> ')!!}
                        </div>

                        <div class="mb-3">
                            <label for="">Correo Electronico inicio sesión</label>
                            <input type="email" name="email" value="{{old('email')}}" id="email" class="form-control form-control-sm">
                            {!!$errors->first('email', '<small class="text-danger"> :message </small> ')!!}
                        </div>

                        <div class="mb-3">
                            <label for="">Correo Electronico para mostrar</label>
                            <input type="email" name="correo" value="{{old('correo')}}" id="correo" class="form-control form-control-sm">
                            {!!$errors->first('correo', '<small class="text-danger"> :message </small> ')!!}
                        </div>

                        <div class="mb-3">
                            <label for="">Celular</label>
                            <input type="tel" name="celular" value="{{old('celular')}}" id="celular" class="form-control form-control-sm">
                            {!!$errors->first('celular', '<small class="text-danger"> :message </small> ')!!}
                        </div>

                        <div class="mb-3">
                            <label for="">Extensión</label>
                            <input type="tel" name="extension" value="{{old('extension')}}" id="extension" class="form-control form-control-sm">
                            {!!$errors->first('extension', '<small class="text-danger"> :message </small> ')!!}
                        </div>

                        <div class="mb-3 mx-3">
                            <div class="row d-flex align-items-center">
                                <div class="col-12">
                                    <label for="">Contraseña</label>
                                </div>
                                <div class="col-9 p-0 m-0">
                                    <input type="password" name="password" id="password" value="{{old('password')}}" class="form-control form-control-sm">
                                </div>
                                <div class="col-1 p-0 my-0 mx-3">
                                    <input type="checkbox" id="view_password">
                                </div>
                                {!!$errors->first('password', '<small class="text-danger"> :message </small>')!!}
                            </div>


                        </div>

                        <div class="mb-3">
                            <label for="">Puesto</label>
                            <input type="text" name="puesto" value="{{old('puesto')}}" class="form-control form-control-sm">
                            {!!$errors->first('puesto', '<small class="text-danger"> :message </small>')!!}
                        </div>


                        <div class="mb-3">
                            <label for="">Planta</label>
                            <select name="planta" id="" class="form-control form-control-sm">
                                <option value="P1">Planta 1</option>
                                <option value="P2">Planta 2</option>
                                <option value="P3">Planta 3</option>
                            </select>
                            {!!$errors->first('planta', '<small class="text-danger"> :message </small>')!!}
                        </div>



                        <div class="mb-3">
                            <label for="" class="fw-bold">Evaluaciones de proveedores</label>
                            <small class="text-muted d-block mb-2">Marca la encuesta y despues las subencuestas que contestara el usuario.</small>
                            <div class="border rounded p-2">
                                @forelse ($evaluaciones as $evaluacion)
                                    <div class="form-check">
                                        <input class="form-check-input eval-checkbox" type="checkbox" name="evaluaciones[]" value="{{$evaluacion->id}}" id="eval_create_{{$evaluacion->id}}" data-eval="{{$evaluacion->id}}">
                                        <label class="form-check-label" for="eval_create_{{$evaluacion->id}}">
                                            {{$evaluacion->titulo}} <small class="text-muted">({{$evaluacion->seccionesActivas->count()}} subencuestas)</small>
                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="small text-muted contador-secciones" data-container="secciones_create_{{$evaluacion->id}}"></span>
                                        <span class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sec-all" data-container="secciones_create_{{$evaluacion->id}}">Todas</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sec-none" data-container="secciones_create_{{$evaluacion->id}}">Ninguna</button>
                                        </span>
                                    </div>
                                    <div class="ms-4 mb-2 secciones-container" id="secciones_create_{{$evaluacion->id}}">
                                        @forelse ($evaluacion->seccionesActivas as $seccion)
                                            <div class="form-check">
                                                <input class="form-check-input seccion-checkbox" type="checkbox" name="secciones[{{$evaluacion->id}}][]" value="{{$seccion->id}}" id="sec_create_{{$evaluacion->id}}_{{$seccion->id}}" disabled>
                                                <label class="form-check-label" for="sec_create_{{$evaluacion->id}}_{{$seccion->id}}">
                                                    {{$seccion->titulo}}
                                                </label>
                                            </div>
                                        @empty
                                            <small class="text-muted">Sin secciones</small>
                                        @endforelse
                                    </div>
                                @empty
                                    <span class="text-muted">No hay evaluaciones activas</span>
                                @endforelse
                                {!!$errors->first('secciones', '<div class="text-danger small fw-bold mt-2">:message</div>')!!}
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-success">Agregar</button>
                        </div>

                    </div>

                </div>
             </form>

            <form action="{{route('usuarios.excel')}}" method="POST" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="mb-2">
                    <label for="" class="fw-bold">Importar usuarios desde Excel (.xlsx)</label>
                    <input type="file" name="archivo" accept=".xlsx" class="form-control form-control-sm">
                    <small class="form-text text-muted">
                        Si hay cualquier problema (un correo repetido, un correo inválido, un campo vacío)
                        no se importa <strong>ningún</strong> usuario y se indica la fila exacta a corregir.
                    </small>
                </div>
                @if ($errors->has('archivo'))
                    <div class="alert alert-danger py-2 px-3 mb-2">
                        @foreach ($errors->get('archivo') as $error)
                            <div>{!! nl2br(e($error)) !!}</div>
                        @endforeach
                    </div>
                @endif
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Importar desde Excel</button>
                    <a href="{{route('usuarios.plantilla')}}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-file-excel-o"></i> Descargar plantilla
                    </a>
                </div>
            </form>

         </div>
        {{-- div que encierra el formulario del usuario --}}
        
        <div class="col-sm-12 col-md-7 col-lg-9 bg-white p-4 bg-white  shadow-sm">
            <h3>Lista de usuarios</h3>
            @if (session('eliminado'))
                <h4 class="text-success fw-bold">{{session('eliminado')}}</h4>
            @endif
            @if (session('editado'))
                @php $editadoEsError = str_contains(session('editado'), 'no se asignaron'); @endphp
                <h4 class="{{ $editadoEsError ? 'text-danger' : 'text-success' }} fw-bold">
                    <i class="fa {{ $editadoEsError ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                    {{session('editado')}}
                </h4>
            @endif
            @foreach ($errors->keys() as $clave)
                @if ($clave !== 'archivo')
                    @foreach ($errors->get($clave) as $error)
                        <h4 class="text-danger">{{$error}}</h4>
                    @endforeach
                @endif
            @endforeach

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                @forelse ($usuarios as $usuario)
                    <div class="col">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        @if ($usuario->planta == 'Planta 1')
                                            <i class="fa-solid fa-user-circle fa-3x text-primary"></i>
                                        @elseif ($usuario->planta == 'Planta 2')
                                            <i class="fa-solid fa-user-circle fa-3x text-success"></i>
                                        @else
                                            <i class="fa-solid fa-user-circle fa-3x text-warning"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-bold mb-0">{{$usuario->name}}</h5>
                                        <small class="text-muted">{{$usuario->puesto}}</small>
                                    </div>
                                </div>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1">
                                        <i class="fa fa-envelope me-2 text-muted"></i>{{$usuario->correo}}
                                    </li>
                                    <li class="mb-1">
                                        <i class="fa fa-phone me-2 text-muted"></i>Ext. {{$usuario->extension}}
                                    </li>
                                    <li>
                                        <i class="fa fa-building me-2 text-muted"></i>{{$usuario->planta}}
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer bg-white border-top-0 d-flex justify-content-end gap-2">
                                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#ac{{$usuario->id}}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#e{{$usuario->id}}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fa-solid fa-users fa-4x mb-3"></i>
                        <h5>No hay usuarios registrados</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>



{{-- aqui van a estar los modales para la edicion y la eliminacion de los usuarios --}}
@forelse ($usuarios as $usuarie)
    
    <!-- Modal -->
    <div class="modal fade" id="e{{$usuarie->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>¿Desea eliminar al usuario? </h4>
                </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <form action="{{route('usuario.eliminar', $usuarie->id)}}" method="POST">
                @csrf @method('PATCH')
                <button class="btn btn-success">
                    <i class="fa fa-check"></i>
                    Confirmar
                    </button>
                </form>
                
            </div>
        </div>
        </div>
    </div>
    <!-- Modal -->


        <!-- Modal -->
    <div class="modal fade" id="ac{{$usuarie->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-md ">
                <div class="modal-content">
                     <div class="modal-header bg-dark text-white h5 ">Actualizando Usuario</div>
                        <div class="modal-body">
                            <div class="row justify-content-center">                                
                            <form action="{{route('actualizar.usuario', $usuarie->id)}}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="usuario_id_edit" value="{{$usuarie->id}}">
                                <input type="hidden" name="evaluaciones_tocadas" value="0">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="">Nombre completo</label>
                                        <input type="text" name="nombre_edit" class="form-control " value="{{old('nombre_edit', $usuarie->name)}}">
                                        {!!$errors->first('nombre_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Correo Electronico iniciar sesion</label>
                                        <input type="email" name="email_edit" value="{{old('email_edit', $usuarie->email)}}" id="email" class="form-control ">
                                        {!!$errors->first('email_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>

                                    <div class="mb-3">
                                        <label for="">Correo Electronico Mostrar</label>
                                        <input type="email" name="correo_edit" value="{{old('correo_edit', $usuarie->correo)}}" id="email" class="form-control ">
                                        {!!$errors->first('correo_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Celular</label>
                                        <input type="tel" name="celular_edit" value="{{old('celular_edit', $usuarie->celular)}}" id="celular" class="form-control ">
                                        {!!$errors->first('celular_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>
            
                                    <div class="mb-3">
                                        <label for="">Extensión</label>
                                        <input type="tel" name="extension_edit" value="{{old('extension_edit', $usuarie->extension)}}" id="extension" class="form-control ">
                                        {!!$errors->first('extension_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>

                                    <div class="mb-3 mx-3">
                                        <div class="row d-flex align-items-center">
                                            <div class="col-12">
                                                <label for="">Contraseña</label>
                                            </div>
                                            <div class="col-9 p-0 m-0">
                                                <input type="password" name="password_edit" id="password1" value="{{old('password_edit', $usuarie->password)}}" class="form-control ">
                                            </div>
                                            <div class="col-1 p-0 my-0 mx-3">
                                                <input type="checkbox" id="view_password1">
                                            </div>
                                            {!!$errors->first('password_edit', '<small class="text-danger"> :message </small>')!!}
                                        </div>
                                        
                                        
                                    </div>

                                    <div class="mb-3">
                                        <label for="">Puesto</label>
                                        <input type="tel" name="puesto_edit" value="{{old('puesto_edit', $usuarie->puesto)}}"  class="form-control ">
                                        {!!$errors->first('puesto_edit', '<small class="text-danger"> :message </small> ')!!}
                                    </div>

                                    
                                    <div class="mb-3">
                                        <label for="">Planta </label>
                                        <select name="planta_edit" id="" class="form-control ">
                                            @php
                                                $opcionesPlanta = ['P1', 'P2', 'P3'];
                                                $etiquetasPlanta = ['P1' => 'Planta 1', 'P2' => 'Planta 2', 'P3' => 'Planta 3'];
                                                if ($usuarie->planta && !in_array($usuarie->planta, $opcionesPlanta, true)) {
                                                    $opcionesPlanta[] = $usuarie->planta;
                                                }
                                            @endphp
                                            @foreach ($opcionesPlanta as $opcionPlanta)
                                                <option value="{{ $opcionPlanta }}" {{ old('planta_edit', $usuarie->planta) === $opcionPlanta ? 'selected' : '' }}>
                                                    {{ $etiquetasPlanta[$opcionPlanta] ?? $opcionPlanta }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="" class="fw-bold">Evaluaciones de proveedores</label>
                                        <small class="text-muted d-block mb-2">Marca la encuesta y despues las subencuestas que contestara el usuario.</small>
                                        <div class="border rounded p-2">
                                            @forelse ($evaluaciones as $evaluacion)
                                                @php
                                                    $asignada = $usuarie->evaluaciones->firstWhere('id', $evaluacion->id);
                                                    $pivotSecciones = $asignada ? $asignada->pivot->secciones : [];
                                                    if (is_string($pivotSecciones) && $pivotSecciones !== '') { $pivotSecciones = json_decode($pivotSecciones, true); }
                                                    $seccionesAsignadas = is_array($pivotSecciones) ? array_map('intval', $pivotSecciones) : [];
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input eval-checkbox-edit" type="checkbox" name="evaluaciones_edit[]" value="{{$evaluacion->id}}" id="eval_edit_{{$usuarie->id}}_{{$evaluacion->id}}" data-eval="{{$evaluacion->id}}" data-user="{{$usuarie->id}}" {{ $asignada ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="eval_edit_{{$usuarie->id}}_{{$evaluacion->id}}">
                                                        {{$evaluacion->titulo}} <small class="text-muted">({{$evaluacion->seccionesActivas->count()}} subencuestas)</small>
                                                    </label>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <span class="small text-muted contador-secciones" data-container="secciones_edit_{{$usuarie->id}}_{{$evaluacion->id}}"></span>
                                                    <span class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary btn-sec-all" data-container="secciones_edit_{{$usuarie->id}}_{{$evaluacion->id}}">Todas</button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sec-none" data-container="secciones_edit_{{$usuarie->id}}_{{$evaluacion->id}}">Ninguna</button>
                                                    </span>
                                                </div>
                                                <div class="ms-4 mb-2 secciones-container-edit" id="secciones_edit_{{$usuarie->id}}_{{$evaluacion->id}}">
                                                    @forelse ($evaluacion->seccionesActivas as $seccion)
                                                        <div class="form-check">
                                                            <input class="form-check-input seccion-checkbox-edit" type="checkbox" name="secciones_edit[{{$evaluacion->id}}][]" value="{{$seccion->id}}" id="sec_edit_{{$usuarie->id}}_{{$evaluacion->id}}_{{$seccion->id}}" {{ in_array((int)$seccion->id, $seccionesAsignadas) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="sec_edit_{{$usuarie->id}}_{{$evaluacion->id}}_{{$seccion->id}}">
                                                                {{$seccion->titulo}}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <small class="text-muted">Sin secciones</small>
                                                    @endforelse
                                                </div>
                                            @empty
                                                <span class="text-muted">No hay evaluaciones activas</span>
                                            @endforelse
                                            {!!$errors->first('secciones_edit', '<div class="text-danger small fw-bold mt-2">:message</div>')!!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="modal-footer">
                                <button class="btn btn-success">
                                    <i class="fa fa-check"></i>
                                    Confirmar
                                </button>
                                    </form>
                            
                            
                                
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                
                            </div>
                    </div>
                </div>
            </div>
        <!-- Modal -->


  @empty
      
  @endforelse
{{-- aqui van a estar los modales para la edicion y la eliminacion de los usuarios --}}









<script>
{
  const   $password = document.getElementById('password');
  const   $ver = document.getElementById('view_password');


  if ($ver && $password) {
    $ver.onchange = function(e){
      $password.type = $ver.checked ? "text" : "password"
    }
  }
}
{
  const   $password = document.getElementById('password1');
  const   $ver = document.getElementById('view_password1');


  if ($ver && $password) {
    $ver.onchange = function(e){
      $password.type = $ver.checked ? "text" : "password"
    }
  }

}

// ---- Checkboxes de secciones por evaluacion (formulario crear) ----
{
  function toggleSeccionesCrear(evalBox) {
    const cont = document.getElementById('secciones_create_' + evalBox.dataset.eval);
    if (!cont) return;
    cont.querySelectorAll('.seccion-checkbox').forEach(cb => {
      cb.disabled = !evalBox.checked;
      if (!evalBox.checked) cb.checked = false;
    });
    actualizarContadores();
  }
  document.querySelectorAll('.eval-checkbox').forEach(cb => {
    cb.addEventListener('change', () => toggleSeccionesCrear(cb));
    toggleSeccionesCrear(cb);
  });
}

// ---- Checkboxes de secciones por evaluacion (modal editar) ----
{
  function toggleSeccionesEditar(evalBox) {
    const cont = document.getElementById('secciones_edit_' + evalBox.dataset.user + '_' + evalBox.dataset.eval);
    if (!cont) return;
    cont.querySelectorAll('.seccion-checkbox-edit').forEach(cb => {
      cb.disabled = !evalBox.checked;
      if (!evalBox.checked) cb.checked = false;
    });
    actualizarContadores();
  }
  document.querySelectorAll('.eval-checkbox-edit').forEach(cb => {
    cb.addEventListener('change', () => toggleSeccionesEditar(cb));
    toggleSeccionesEditar(cb);
  });
}

// ---- Contador de subencuestas seleccionadas ----
function actualizarContadores() {
  document.querySelectorAll('.contador-secciones').forEach(el => {
    const cont = document.getElementById(el.dataset.container);
    if (!cont) return;
    const total = cont.querySelectorAll('input[type="checkbox"]').length;
    const sel = cont.querySelectorAll('input[type="checkbox"]:checked').length;
    el.textContent = sel + ' de ' + total + ' subencuestas seleccionadas';
  });
}

// ---- Botones Todas / Ninguna ----
document.querySelectorAll('.btn-sec-all, .btn-sec-none').forEach(btn => {
  btn.addEventListener('click', function() {
    const cont = document.getElementById(btn.dataset.container);
    if (!cont) return;
    const marcar = btn.classList.contains('btn-sec-all');
    cont.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      cb.checked = marcar;
      cb.disabled = false;
    });
    const formBtn = btn.closest('form');
    const flag = formBtn ? formBtn.querySelector('[name="evaluaciones_tocadas"]') : null;
    if (flag) flag.value = '1';
    actualizarContadores();
  });
});

// ---- Marcar el bloque de encuestas como "tocado" ----
document.querySelectorAll('.eval-checkbox, .eval-checkbox-edit, .seccion-checkbox, .seccion-checkbox-edit').forEach(cb => {
  cb.addEventListener('change', function() {
    const form = cb.closest('form');
    const flag = form ? form.querySelector('[name="evaluaciones_tocadas"]') : null;
    if (flag) flag.value = '1';
    actualizarContadores();
  });
});

actualizarContadores();

// Validacion: evaluacion marcada debe tener al menos una seccion
function contenedorSecciones(ev) {
  const user = ev.dataset.user;
  const evalId = ev.dataset.eval;
  if (user) return document.getElementById('secciones_edit_' + user + '_' + evalId);
  return document.getElementById('secciones_create_' + evalId);
}
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    const boxes = form.querySelectorAll('.eval-checkbox:checked, .eval-checkbox-edit:checked');
    for (const ev of boxes) {
      const cont = contenedorSecciones(ev);
      if (cont && !cont.querySelector('.seccion-checkbox:checked') && !cont.querySelector('.seccion-checkbox-edit:checked')) {
        alert('La evaluación debe tener al menos una sección seleccionada.');
        e.preventDefault();
        ev.checked = false;
        if (ev.classList.contains('eval-checkbox')) toggleSeccionesCrear(ev);
        if (ev.classList.contains('eval-checkbox-edit')) toggleSeccionesEditar(ev);
      }
    }
  });
});



</script>

{{-- Reabre el modal de edicion cuando la validacion falla, para que el error no quede escondido --}}
@if ($errors->any() && old('usuario_id_edit'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('ac' + {{ (int) old('usuario_id_edit') }});
        if (modal && window.bootstrap) {
            new bootstrap.Modal(modal).show();
        }
    });
    </script>
@endif


@endsection