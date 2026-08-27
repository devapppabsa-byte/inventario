@extends('layout')
@section('title','Agregar Telefono')
@include('assets.nav')
@section('contenido')
<div class="container">
    <div class="row d-flex justify-content-center p-3 mt-2">

        <div class="col-12 mt-5 bg-white p-4 shadow-lg">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="text-center">Editar teléfono</h2>
                    @if (session('actualizado'))
                        <li class="text-success fw-bold text-center">{{session('actualizado')}}</li>
                    @endif
                </div>
            </div>
            <form action="{{route('telefono.update', $telefono->id)}}" method="POST" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="row d-flex justify-content-center p-lg-4 p-sm-1 m-2">

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4">
                        <label for="" class="fw-bold mb-0 mt-2">Usuario</label>
                        <select name="usuario" value="{{old('usuario')}}"  class="form-control form-control-sm">
                            <option value="" selected>Selecciona un usuario</option>
                            @forelse ($usuarios as $usuario)
                                <option value="{{$usuario->id}}" {{($telefono->user->name == $usuario->name) ? 'selected' : ''}} >{{$usuario->name}} | {{$usuario->puesto}}</option>  
                            @empty
                                <li>No hay resultados</li>
                            @endforelse
                        </select>
                        @error('area')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2">Marca</label>
                            <input type="text" value="{{old('marca', $telefono->marca)}}" name="marca" class="form-control form-control-sm">
                            
                            @error('marca')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2">Modelo</label>
                            <input type="text" value="{{old('modelo', $telefono->modelo)}}" name="modelo" class="form-control form-control-sm">
                            
                            @error('modelo')
                                <small class="text-danger">
                                    {{$message}}
                                </small>
                            @enderror

                        </div>
                    </div>



                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2">Número de serie</label>
                            <input type="text" name="serie" value="{{old('serie', $telefono->serie)}}" class="form-control form-control-sm">
                            
                            @error('serie')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror

                        </div>
                    </div>



                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-5 mt-2">
                        <label for="" class=" m-0 fw-bold">¿Comparte con alguien?</label>
                        <select name="comparte" value="{{old('comparte')}}" id="" class="form-control form-control-sm">
                            <option value="Con Nadie">Nadie</option>
                            @forelse ($usuarios as $usuario)
                                <option value="{{$usuario->name}}" {{($usuario->name == $telefono->comparte) ? 'selected' : ''}}>{{$usuario->name}} | {{ $usuario->puesto }}</option>
                            @empty
                                <li>No hay datos</li>
                            @endforelse

                        </select>
                        @error('comparte')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror
                    </div>


                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2" >Tipo</label>
                            <select name="tipo" id="" class="form-select form-control form-control-sm">
                                <option value="Linea" {{($telefono->tipo == 'Linea' ? 'selected' : '')}}>Linea</option>
                                <option value="Celular" {{($telefono->tipo == 'Celular' ? 'selected' : '')}}>Celular</option>
                            </select>
                            
                            @error('tipo')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2" >Estado</label>
                            <select name="estado" id="" class="form-select form-control form-control-sm">
                                <option value="1" {{($telefono->nuevo == 1) ? 'selected' : ''}}>Nuevo</option>
                                <option value="0" {{($telefono->nuevo == 0) ? 'selected' : ''}}>Usado</option>
                            </select>
                            
                            @error('estado')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror

                        </div>
                    </div>



                    <div class="col-12 col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0 mt-2">Observaciones</label>
                            <textarea name="observaciones" class="w-100 form-control">{{old('observaciones', $telefono->observaciones)}}</textarea>
                            @error('observaciones')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div> 

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="imagen1" id="imagen0">
                        </div>
                        @error('imagen1')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror

                        <div class="col-12 p-3 shadow" id="previa0">
                            <img id="img_tag0" class="img-fluid" src="{{Storage::url($telefono->imagen1)}}" alt="">
                        </div>

                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="mb-3">
                            <input type="file" class="imagen form-control" name="imagen2" id="imagen1">
                        </div>
                        @error('imagen2')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror
                        <div class="col-12 p-3 shadow" id="previa1">
                            <img id="img_tag1" class="img-fluid" alt="" src="{{Storage::url($telefono->imagen2)}}">
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="mb-3">
                            <input type="file" class="imagen form-control" name="imagen3" id="imagen2">
                        </div>
                        @error('imagen3')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror
                        <div class="col-12 p-3 shadow" id="previa2">
                            <img id="img_tag2" class="imagen img-fluid" alt="" src="{{Storage::url($telefono->imagen3)}}">
                        </div>
                    </div>
                    <div class="col-12 text-start mt-3">
                        <div class="mb-3">
                            <button class="btn btn-dark  fw-bold">
                                <i class="fas fa-edit mx-2"></i>
                                 Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection