@extends('layout')
@section('title', 'Buscador')
@section('contenido')
    
<div class="container ">


{{-- Boton del login --}}
    <div class="row flotante">
        <div class="col-12">
            <button class="btn btn-info text-white rounded-pill" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="fa fa-key me-2"></i>
                <span class="fw-bold" >
                    Login
                </span>
            </button>
        </div>
    </div>
{{-- Termina boton del login --}}



<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title text-white" id="exampleModalLabel">
              Entrar
            </h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

<form action="">
              <div class="mb-3 mx-5">
                  <label for="" class="fw-bold">Usuario: </label>
                  <input type="text" class="form-control">
              </div>

              <div class="mb-3 mx-5">
                <label for="" class="fw-bold">Contraseña: </label>
                <input type="text" class="form-control">
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary">Entrar</button>
        </div>
</form>
      </div>
    </div>
  </div>
  {{-- Termina modal del login --}}















    <form action="{{route('home.buscar')}}" method="POST" enctype="multipart/form-data">
        @csrf
    <div class="row pt-5 d-flex justify-content-center">
 

        <div class="col-8">
            <div class="row justify-centent-center mt-1 fw-bold">

                <div class="col-12 text-center ">
                   <h4 class="fw-bold p-3">
                    Total de dispositivosa en Grupo PABSA
                   </h4>
                </div>

                <div class="col-4">
                    <div class="card">
                        <div class="card-header p-1 bg-danger text-white">
                            <i class="fa fa-desktop text-white me-2"></i>
                           Computadoras 
                        </div>
                        <div class="card-body text-center p-1">
                            @if (isset($contadorComputadoras))
                            {{$contadorComputadoras}}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <div class="card-header p-1 bg-primary text-white">
                            <i class="fa fa-print text-white me-2"></i>
                            Impresoras
                        </div>
                        <div class="card-body text-center p-1">
                            @if (isset($contadorImpresoras))
                                {{$contadorImpresoras}}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <div class="card-header p-1 bg-success text-white">
                            <i class="fa fa-charging-station text-white me-2"></i>
                           Reguladores 
                        </div>
                        <div class="card-body text-center p-1">
                            @if (isset($contadorReguladores))
                                {{$contadorReguladores}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 text-center mt-5">
            <img src="img/logo.png" class=" img-fluid" style="width: 150px;" alt="">
        </div>

 
        <div class="col-4 col-12 p-3 mt-1">
            <div class="row justify-content-center">
                <div class="col-lg-2 col-md-3 col-sm-4 text-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input checkbox" type="checkbox" name="computadoras" id="computadoras">
                        <label class="form-check-label" for="computadoras">
                          <strong class="h4"> Computadoras </strong>
                        </label>
                      </div>
                </div>
                <div class="col-4 col-lg-2 col-md-3 col-sm-4 text-center">
                    <div class="form-check">
                        <input class="form-check-input checkbox" type="checkbox" name="reguladores" id="reguladores" >
                        <label class="form-check-label" for="reguladores">
                            <strong class="h4"> Reguladores </strong>
                        </label>
                      </div>
                </div>
                <div class="col-4 col-lg-2 col-md-3 col-sm-4 text-center">
                    <div class="form-check">
                        <input class="form-check-input checkbox" type="checkbox" name="impresoras" id="impresoras" >
                        <label class="form-check-label" for="impresoras">
                            <strong class="h4"> Impresoras </strong>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-3">
                <div class="col-lg-2 col-md-3 col-sm-4 text-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="marca" id="marca">
                        <label class="form-check-label" for="marca">
                          <strong class="h4"> Marca </strong>
                        </label>
                      </div>
                </div>
                <div class="col-4 col-lg-2 col-md-3 col-sm-4 text-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="serie" id="serie" >
                        <label class="form-check-label" for="serie">
                            <strong class="h4"> # Serie </strong>
                        </label>
                      </div>
                </div>
                <div class="col-4 col-lg-2 col-md-3 col-sm-4 text-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="modelo" id="modelo" >
                        <label class="form-check-label" for="modelo">
                            <strong class="h4"> Modelo </strong>
                        </label>
                      </div>
                </div>
            </div>
        </div>
   
        <div class="col-12 col-sm-12 col-md-9 col-lg-6">
            <input 
                type="text"
                name="busqueda" 
                class="form-control form-control-lg rounded-pill shadow-sm" 
                placeholder="buscar"
                autofocus autocomplete="address-level1">
                @error('busqueda')
                    <div class="alert alert-danger alert-sm p-1 mt-3 rounded-pill text-center">
                        {{$message}}
                    </div>
                @enderror
        </div>
        <div class="col-12 text-center mt-2">
            <button class="btn btn-success">
                <i class="fa fa-search"></i>
                Buscar
            </button>
        </div>
   </div>
</form>



        <div class="row mt-5">
            <div class="col-12 h3">
                Resultados:
            </div>
        </div>

        <div class="row justify-content-around">


            @if (isset($resultado))
                
            
            @forelse ($resultado as $itemResultado)
            <div class="col-6 col-lg-2 col-md-4 col-sm-6 bg-white p-4 shadow m-1">
                <div class="row text-start">
    
                    <div class="col-12">
                        <strong> Marca: </strong> {{$itemResultado->marca}}
                    </div>
                    <div class="col-12">
                        <strong> Modelo: </strong> {{$itemResultado->modelo}}
                    </div>
                    <div class="col-12">
                        <strong> Serial: </strong> {{$itemResultado->serie}}
                    </div>
                    <div class="col-12 mt-1">
                        <a href="{{route('device.show', $itemResultado->serie)}}" class="btn btn-primary">
                            <i class="fa fa-eye"></i>
                            Ver detalles
                        </a>
                    </div>
                </div>
            </div>
            @empty
                <div class="alert alert-success alert-sm fw-bold">
                    <i class="fa fa-times-circle"></i>
                    No se encontraron resultados
                </div>

            @endforelse
           
 
            @endif

        </div>
        
   
        
        
      
    </div>
@endsection