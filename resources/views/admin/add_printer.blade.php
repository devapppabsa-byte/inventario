@extends('layout')
@section('title', 'Agregando impresora')
@include('assets.nav')
@section('contenido')


<div class="container">
    <div class="row d-flex justify-content-center p-3 mt-2">
        <div class="col-12 mt-5 bg-white p-lg-4 p-sm-1 border">
            <div class="row">
                <div class="col-12 text-center">
                    <h3>Agregar Impresora</h3>
                    {{$errors->any()}}
                    @if (session('agregado'))
                       <li class="text-success fw-bold"> {{session('agregado')}}</li>
                    @endif

                </div>
            </div>
            <form action="{{route('printer.create')}}" method="POST" enctype="multipart/form-data" id="formulario" onsubmit="deshabilita()">
                @csrf
                <div class="row   p-lg-4 p-sm-1 m-2">
                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4 mb-3">
                        <label for="" class=" m-0 fw-bold">Titular del equipo</label>
                        <select name="usuario" id="" class="form-control form-control-sm">
                            <option value="" selected>Selecciona un usuario</option>
                            @forelse ($usuarios as $usuario)
                                <option value="{{$usuario->id}}">{{$usuario->name}} | {{ $usuario->puesto }}</option>
                            @empty
                                <li>No hay datos</li>
                            @endforelse

                        </select>
                        @error('area')
                        <small class="text-danger">
                            {{$message}}
                        </small>
                        @enderror
                    </div>



                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4 mb-3">
                        <label for="" class=" m-0 fw-bold">¿Comparte con alguien?</label>
                        <select name="comparte" id="" class="form-control form-control-sm">
                            <option value="Con Nadie">Nadie</option>
                            @forelse ($usuarios as $usuario)
                                <option value="{{$usuario->name}}">{{$usuario->name}} | {{ $usuario->puesto }}</option>
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
                            <label for="" class=" m-0 fw-bold">Marca</label>
                            <input type="text" name="marca" value="{{old('marca')}}" class="form-control form-control-sm">
                            @error('marca')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-2">
                        <div class="mb-3">
                            <label for="" class=" m-0 fw-bold">Modelo</label>
                            <input type="text" name="modelo" value="{{'modelo'}}"  class="form-control form-control-sm">
                            @error('modelo')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="mb-3">
                            <label for="" class=" m-0 fw-bold">Laser o Tinta</label>
                            <select name="tipo" class="form-control form-control-sm">
                                <option value="Laser">Laser</option>
                                <option value="Tinta">Tinta</option>
                            </select>

                            @error('tipo')
                                <small class="text-danger">
                                    {{$message}}
                                </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-3">
                        <div class="mb-3">
                            <label for="" class=" m-0 fw-bold">Número de serie</label>
                            <input type="text" name="serie" value="{{old('serie')}}" class="form-control form-control-sm">
                            @error('serie')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>



                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-3">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Estado</label>
                            <select name="estado"  class="form-control form-control-sm">
                                <option value="1">Nuevo</option>
                                <option value="0">Usado</option>
                            </select>   

                            @error('estado')
                                <small class="text-danger p-1">
                                    {{$message}}
                                </small>
                            @enderror
                       
                        </div>
                    </div>



                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-3">
                        <div class="mb-3">
                            <label for="" class=" m-0 fw-bold">Observaciones</label>
                            <input name="observaciones" value="{{old('observaciones')}}" class="form-control form-control-sm" value="{{old('observaciones')}}">
                            @error('observaciones')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>




                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-3 text-center">
                        <div class="mb-3">
                            <label for="" class="m-0 fw-bold">Foto 1</label>
                            <input type="file" class="form-control form-control-sm p-0" id="imagen0" name="imagen1">
                            <div id="previa0">
                                <img  id="img_tag0" class="img-fluid" alt="">
                            </div>
                            @error('imagen1')
                                <small class="text-danger">
                                    {{$message}}
                                </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-3 text-center">
                        <div class="mb-3">
                            <label for="" class="m-0 fw-bold">Foto 2</label>
                            <input type="file" class="form-control form-control-sm p-0" id="imagen1" name="imagen2" >
                            <div id="previa1">
                                <img  id="img_tag1" class="img-fluid" alt="">
                            </div>
                            @error('imagen2')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="mb-3">
                            <button class="btn btn-dark w-25 fw-bold" id="login">
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>







{{-- aqui va el javascript --}}

<script>
    function deshabilita(){
        consol

        const $boton = document.getElementById('button');
        $boton.disable = true;
        $boton.textContent = "Procesando ...";



    }
</script>




@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.mb-3', { opacity: 0, y: 15, duration: 0.4, stagger: 0.06, ease: 'power2.out', delay: 0.2 });
  });
</script>
@endsection