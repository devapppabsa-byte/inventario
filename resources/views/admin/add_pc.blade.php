@extends('layout')
@include('assets.nav')
@section('title', 'Agregando PC')
@section('contenido')

<div class="container">

    <div class="row d-flex justify-content-center p-3 ">
        <div class="col-12">
            <h5>
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            </h5>
            <div class="row">
                <div class="col-4 col-12 col-sm-12 col-md-6 col-lg-4">
                    {{-- <img src="{{asset('https://www.logo.wine/a/logo/Linux/Linux-Logo.wine.svg')}}" class="img-fluid w-50" alt=""> --}}
                </div>
                <div class="col-12 col-12 col-sm-12 col-md-6 col-lg-4 text-center pt-4">
                    @if (session('agregada'))
                        <div class="alert-success text-start fw-bold mt-3 p-3">
                           <i class="fa fa-check-circle me-2"></i>  {{session('agregada')}}
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <div class="col-12 border bg-white  p-3">
            <h2 class="m-3 text-center">Agregar computadora</h2>
            <form action="{{route('pc.create')}}" method="POST" enctype="multipart/form-data" >
                @csrf
                <div class="row  m-lg-5 m-sm-2  ">
                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-1 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Marca</label>
                            <input type="text" value="{{old('marca')}}"  class="form-control form-control-sm" name="marca">

                            @error('marca')
                            <small class="text-danger p-1">
                               {{$message}}
                            </small>
                            @enderror 

                        </div>
                    </div>

                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Procesador</label>
                            <input type="text" value="{{old('procesador')}}"  class="form-control form-control-sm" name="procesador">

                            @error('procesador')
                            <small class="text-danger p-1">
                               {{$message}}
                            </small>
                            @enderror 

                        </div>
                    </div>

                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Modelo</label>
                            <input type="text" value="{{old('modelo')}}"  class="form-control form-control-sm" name="modelo">

                            @error('modelo')
                            <small class="text-danger p-1">
                                {{$message}}
                             </small>
                            @enderror
                        </div>
                    </div>



                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-3 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Sistema Operativo</label>
                            <input type="text"  class="form-control form-control-sm" value="{{old('so')}}"  name="so">
                        
                            @error('so')
                            <small class="text-danger p-1">
                                {{$message}}
                             </small>
                            @enderror
                        
                        </div>
                    </div>



                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-4 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Usuario</label>
                            <select name="usuario" id="" class="form-control form-control-sm">
                                    <option value="" selected>Selecciona un usuario</option>
                                @forelse ($usuarios as $usuario)
                                    <option value="{{$usuario->id}}">{{$usuario->name}} - {{$usuario->puesto}}</option>
                                @empty
                                    
                                @endforelse
                            </select>

                            @error('usuario')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror

                        </div>
                    </div>



                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Tipo</label>
                            <select name="tipo" id="" class="form-control form-control-sm">
                                <option value="AIO">AIO</option>
                                <option value="Escritorio">Escritorio</option>
                                <option value="Laptop">Laptop</option>
                            </select>
                            
                            @error('tipo')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        
                        </div>
                    </div>

                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-3 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Número de serie</label>
                            <input type="text" value="{{old('numero_serie')}}"  class="form-control form-control-sm" name="numero_serie">
                        
                        @error('numero_serie')
                        <small class="text-danger p-1">
                            {{$message}}
                        </small>   
                        @enderror
                        
                        </div>
                    </div>



                    
                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Tamaño de HDD</label>
                            <input type="number" value="{{old('size_hdd')}}" min="0" placeholder="GB" name="size_hdd" class="form-control form-control-sm fw-bold">
                            @error('size_hdd')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-1 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">RAM</label>
                            <input type="number" value="{{old('size_ram')}}"  min="1" name="size_ram" placeholder="GB" class="form-control form-control-sm fw-bold">

                            @error('slot4_ram')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>


                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Tamaño de SSD</label>
                            <input type="number" value="{{old('size_ssd')}}"  name="size_ssd" placeholder="GB" class="form-control form-control-sm fw-bold">
                            
                            @error('size_ssd')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                       
                        </div>
                    </div>

                    <div class="col-12 col-6 col-sm-6 col-md-4 col-lg-2 p-1">
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


                    <div class="col-6  p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm fw-bold w-100 h-50">{{old('observaciones')}}</textarea>
                            @error('observaciones')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                       
                        </div>
                    </div>

                    <div class="col-6  p-1">
                        <div class="mb-3">
                            <label for="" class="fw-bold mb-0">Accesorios</label>
                            <textarea name="accesorios" class="form-control form-control-sm fw-bold w-100 h-50">{{old('accesorios')}}</textarea>
                            @error('accesorios')
                            <small class="text-danger">
                                {{$message}}
                            </small>
                            @enderror
                       
                        </div>
                    </div>



                   <div class="col-12 text-center"> <h3 style="text-decoration:underline">Fotografias</h3> <hr class="w-100"></div>
                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <div class="mb-3">
                            <input type="file"  name="imagen1" class="imagen form-control fw-bold" id="imagen0">
                            <div class="col-12 p-2 shadow" id="previa0">
                                <img class="img-fluid" id="img_tag0" alt="">
                            </div>
                            @error('imagen1')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <div class="mb-3">
                            <input type="file" min="0" name="imagen2" class="imagen form-control fw-bold" id="imagen1">
                            <div class="col-12 p-2 shadow" id="previa1">
                                <img class="img-fluid" id="img_tag1" alt="">
                            </div>
                            @error('imagen2')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <div class="mb-3">
                            <input type="file" min="0" name="imagen3" class="imagen form-control" id="imagen2">
                            <div class="col-12 p-2 shadow" id="previa2">
                                <img class="img-fluid" id="img_tag2" alt="">
                            </div>
                            @error('imagen3')
                            <small class="text-danger p-1">
                                {{$message}}
                            </small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-3  mt-5">
                        <button class="btn btn-dark w-100 fw-bold">
                            <i class="fa fa-plus-circle me-2 "></i>
                            Agregar
                        </button>
                    </div>
         

                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.mb-3', { opacity: 0, y: 15, duration: 0.4, stagger: 0.06, ease: 'power2.out', delay: 0.2 });
  });
</script>
@endsection