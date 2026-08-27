
@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Agregar usuarios')



<div class="container-fluid mt-5">
    
    <div class="row justify-content-center">
        {{-- div que encierra el formulario del usuario --}}
        <div class="col-sm-12 col-md-4  col-lg-2 bg-white p-4 mx-1 border border-5">
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
                                <option value="Planta 1">Planta 1</option>
                                <option value="Planta 2">Planta 2</option>
                                <option value="Planta 3">Planta 3</option>
                            </select>
                            {!!$errors->first('planta', '<small class="text-danger"> :message </small>')!!}
                        </div>



                        <div class="mb-3">
                            <label for="">¿Tiene gente acargo? </label>
                            <select name="jefe" class="form-control form-control-sm">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                            {!!$errors->first('jefe', '<small class="text-danger"> :message </small>')!!}
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-success">Agregar</button>
                        </div>

                    </div>

                </div>
             </form>
        </div>
        {{-- div que encierra el formulario del usuario --}}
        
        <div class="col-sm-12 col-md-7 col-lg-9 bg-white p-4 bg-white mx-1 border border-4 tabla-usuarios-scroll">
            <h3>Lista de usuarios</h3>
            @if (session('eliminado'))
                <h4 class="text-success fw-bold">{{session('eliminado')}}</h4>
            @endif
            @if (session('editado'))
                <h4 class="text-success fw-bold">{{session('editado')}}</h4>
            @endif
            @foreach ($errors->all() as $error)
                <h4 class="text-danger">{{$error}}</h4>
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
                                        <i class="fa fa-envelope me-2 text-muted"></i>{{$usuario->email}}
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
                                        <select name="planta_edit" value={{old('planta_edit', $usuarie->planta)}} id="" class="form-control ">
                                            @if ($usuarie->planta == 'Planta 3')
                                                <option value="Planta 3">Planta 3</option>
                                                <option value="Planta 2">Planta 2</option>
                                                <option value="Planta 1">Planta 1</option>
                                            @endif
                                            
                                            @if ($usuarie->planta == 'Planta 2')
                                                <option value="Planta 2">Planta 2</option>
                                                <option value="Planta 1">Planta 1</option>
                                                <option value="Planta 3">Planta 3</option>
                                            @endif

                                            @if ($usuarie->planta == 'Planta 1')
                                                <option value="Planta 1">Planta 1</option>
                                                <option value="Planta 2">Planta 2</option>
                                                <option value="Planta 3">Planta 3</option>
                                            @endif

                                        </select>
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


  $ver.onchange = function(e){
    $password.type = $ver.checked ? "text" : "password"
  }
}

{

  const   $password = document.getElementById('password1');
  const   $ver = document.getElementById('view_password1');


  $ver.onchange = function(e){
    $password.type = $ver.checked ? "text" : "password"
  }

}

</script>


@endsection