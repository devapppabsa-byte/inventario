@extends('layout')
@section('contenido')
@section('title', 'Resguardo')
@include('assets.nav')


<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-4 text-center h5">
      <a href="{{route('agregar.usuarios')}}">
        <i class="fa fa-arrow-left"></i>
        Volver
      </a>
    </div>
  </div>
</div>

<!-- encabezado -->
<div class="container bg-white p-3 pt-5 mt-2 border shadow">{{--  este es el contenedor de todo --}}


  <div class="container px-5 py-2 ">
      <div class="row border border-5">
        
        <div class="col-sm-12 col-md-4  col-lg-4 centrar-verticalmente p-2 mt-2">
          <img src="{{asset('img/logo.png')}}" class="mx-auto img-fluid" alt="">
        </div>
        
        <div class="col-sm-12 col-md-4 col-lg-4 border mt-2"> 
          <div class="row text-center border">
            <div class="col-12 text-center">
              <h3>FORMATO</h3>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-12 text-center">
              <span>
                ASIGNACIÓN DE DISPOSITIVOS
              </span>
            </div>
          </div>
        </div>
    
        <div class=" col-sm-12 col-md-4 col-lg-4 border mt-2">
          <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-4 border fw-bold text-center">Código</div>
            <div class="col-sm-6 col-md-8 col-lg-8 border p-0 text-center" >FO/GP/SIS/001/02</div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-4 border fw-bold text-center">Versión</div>
            <div class="col-sm-6 col-md-8 col-lg-8 border p-0 text-center" >02</div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-4 border fw-bold text-center">Vigencia</div>
            <div class="col-sm-6 col-md-8 col-lg-8 border p-0 text-center" >
              ENERO 2026</div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-4 border fw-bold text-center">Página</div>
            <div class="col-sm-6 col-md-8 col-lg-8 border p-0 text-center" >1 de 1</div>
          </div>
    
        </div>
      
      </div>
    </div>
    <!-- encabezado -->




    <div class="container ">

      <div class="row">
        <div class="col-12 p-2 text-center">
          <h4 class="fw-bold ">
            Resguardo de Dispositivos
          </h4>
        </div>
      </div>

  {{-- conjunto de datos del usuario --}}
      <div class="row px-2">
        <div class="col-12 text-center" style="background-color: rgb(213, 232, 226)">
          <h5 class="mt-2 fw-bold">Datos del Usuario</h5>
        </div>
      </div>


      <div class="row p-0 border-bottom justify-content-center">

        <div class="col-4 p-3 font-size-18 text-center">
          <b class="p-0 m-0">Nombre: </b> 
          <span>{{$usuario->name}}</span>
        </div>

        <div class="col-4 p-3 font-size-18 text-center">
          <b class="p-0 m-0">Puesto: </b> 
          <span>{{$usuario->puesto}}</span>
        </div>

        <div class="col-4 p-3 font-size-18 text-center">
          <b class="p-0 m-0"> Planta: </b> 
          <span>{{$usuario->planta}}</span>
        </div>

      </div>
  {{-- conjunto de datos del usuario --}}




  {{-- conjunto de dartos del equipo de computo --}}
  @forelse ($computadoras as $computadora)

      <div class="row mt-4 px-2 border-top border-bottom">
        <div class="col-9 text-center" style="background-color: rgb(244, 233, 223)">
          <h5 class="mt-2 fw-bold">Datos del Equipo de Cómputo</h5>
        </div>
        <div class="col-3 mt-2">
          <h5>  ¿Equipo Nuevo?  <b>
            @if ($computadora->estado)
              <i class="fa fa-check-circle mx-1 text-success"></i> Si               
            @else
              <i class="fa fa-xmark mx-1 text-danger"></i> No               
              
            @endif
            
            
            </b></h5>
        </div>
      </div>



      <div class="row p-3 border-bottom  mb-2">

        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">Marca: </b> 
          <span class="font-size-18">{{$computadora->marca}}</span>
        </div>

        <div class="col-3 p-1 font-size-18">
          <b class="p-0 m-0">Modelo: </b> 
          <span>{{$computadora->modelo}}</span>
        </div>

        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">RAM: </b> 
          <span>{{$computadora->ram}} GB</span>
        </div>

        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">HDD: </b> 
          <span> {{$computadora->size_hdd}} GB</span>
        </div>

        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">SSD: </b> 
          <span>{{$computadora->size_ssd}} GB</span>
        </div>

        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">Tipo: </b> 
          <span>{{$computadora->tipo}}</span>
        </div>

        <div class="col-3 p-1 font-size-18">
          <b class="p-0 m-0">Número de serie: </b> 
          <span>{{$computadora->numero_serie}}</span>
        </div>

        <div class="col-3 p-1 font-size-18">
          <b class="p-0 m-0">Procesador: </b> 
          <span> {{$computadora->procesador}}</span>
        </div>


        <div class="col-12 p-1 font-size-18">
          <b class="p-0 m-0">Accesorios: </b> 
          <span>Teclado y Mouse Inhalambricos</span>
        </div>

        <div class="col-12 text-center p-1 font-size-18">
          <a  data-bs-toggle="collapse" href="#c{{$computadora->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" 
            aria-controls="multiCollapseExample1">
            <i class="fa fa-image"></i>
            Ver fotos
          </a>
        </div>

      </div>

      <div class="collapse multi-collapse" id="c{{$computadora->id}}">
        <div class="row p-3 justify-content-center">
      
          <div class="col-4 text-center">
            @if (strlen(Storage::url($computadora->imagen1)) > 9 )
              <img src="{{Storage::url($computadora->imagen1)}}" class="img-fluid" alt="">  
            @else
              <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
            @endif
          </div>
          
          <div class="col-4 text-center">
            @if (strlen(Storage::url($computadora->imagen2)) > 9 )
              <img src="{{Storage::url($computadora->imagen2)}}" class="img-fluid" alt="">  
            @else
              <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
            @endif
          </div>

          <div class="col-4 text-center">
            @if (strlen(Storage::url($computadora->imagen3)) > 9 )
              <img src="{{Storage::url($computadora->imagen3)}}" class="img-fluid" alt="">  
            @else
              <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
            @endif
          </div>
          
        </div>
      </div>



    @empty
      <li>No hay datos</li>
    @endforelse
  {{-- conjunto de dartos del equipo de computo --}}


















  {{-- conjunto de dartos de la impresora --}}
  @forelse ($impresoras as $impresora)
      
    <div class="row mt-4 px-2 border-top border-bottom">
      <div class="col-9 text-center" style="background-color: rgb(213, 232, 226)">
        <h5 class="mt-2 fw-bold">Datos del Equipo de Impresora</h5>
      </div>

        <div class="col-3 mt-2">
          <h5>  ¿Equipo Nuevo?  <b>
            @if ($impresora->estado)
              <i class="fa fa-check-circle mx-1 text-success"></i> Si               
            @else
              <i class="fa fa-xmark mx-1 text-danger"></i> No               
              
            @endif
            
            
            </b></h5>
        </div>

      </div>


      <div class="row p-3 border ">
        
        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">Marca: </b> 
          <span class="font-size-18">{{$impresora->marca}}</span>
        </div>
        
        <div class="col-2 p-1 font-size-18">
          <b class="p-0 m-0">Modelo: </b> 
          <span class="font-size-18">{{$impresora->modelo}}</span>
        </div>
        
        <div class="col-4 p-1 font-size-18">
          <b class="p-0 m-0">Número de serie: </b> 
          <span class="font-size-18">{{$impresora->serie}}</span>
        </div>
        
        <div class="col-3 p-1 font-size-18">
          <b class="p-0 m-0">Tipo: </b> 
          <span class="font-size-18">{{$impresora->tipo}}</span>
        </div>

        <div class="col-12 text-center p-1 font-size-18">
          <a  data-bs-toggle="collapse" href="#imp{{$impresora->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" 
            aria-controls="multiCollapseExample1">
            <i class="fa fa-image"></i>
            Ver fotos
          </a>
        </div>
        
      </div>

      <div class="collapse multi-collapse" id="imp{{$impresora->id}}">
        <div class="row p-3 justify-content-center">
      
          <div class="col-6 text-center">
            @if (strlen(Storage::url($impresora->imagen1)) > 9 )
              <img src="{{Storage::url($impresora->imagen1)}}" class="img-fluid" alt="">  
            @else
              <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
            @endif
          </div>
          
          <div class="col-6 text-center">
            @if (strlen(Storage::url($impresora->imagen2)) > 9 )
              <img src="{{Storage::url($impresora->imagen2)}}" class="img-fluid" alt="">  
            @else
              <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
            @endif
          </div>
      
          
        </div>
      </div>

      






    @empty
        <li>No hay datos</li>
    @endforelse
    {{-- cionjunto de datos de la impresora --}}




  {{-- conjunto de datos de el telefono --}}
  @forelse ($telefonos as $telefono)
      
  <div class="row mt-4 px-2 border-top border-bottom">
    <div class="col-9 text-center" style="background-color: rgb(244, 233, 223)">
      <h5 class="mt-2 fw-bold">Datos del Equipo de el Teléfono</h5>
    </div>
    <div class="col-3 mt-2 ">
      <h5>  ¿Equipo Nuevo?  
      <b>
        @if ($telefono->estado)
          <i class="fa fa-check-circle mx-1 text-success"></i> Si               
        @else
          <i class="fa fa-xmark mx-1 text-danger"></i> No               
        @endif
      </b>
    </h5>
      
    </div>
  </div>

  
  <div class="row p-3">
    
    <div class="col-3 p-1 font-size-18">
      <b class="p-0 m-0">Marca: </b> 
      <span class="font-size-18">{{$telefono->marca}}</span>
    </div>
    
    <div class="col-3 p-1 font-size-18">
      <b class="p-0 m-0">Modelo: </b> 
      <span class="font-size-18">{{$telefono->modelo}}</span>
    </div>
    
    <div class="col-4 p-1 font-size-18">
      <b class="p-0 m-0">Número de serie: </b> 
      <span class="font-size-18">{{$telefono->serie}}</span>
    </div>
    
    <div class="col-2 p-1 font-size-18">
      <b class="p-0 m-0">Tipo: </b> 
      <span class="font-size-18">{{$telefono->tipo}}</span>
    </div>

    <div class="col-12 text-center p-1 font-size-18">
      <a  data-bs-toggle="collapse" href="#tel{{$telefono->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" 
        aria-controls="multiCollapseExample1">
        <i class="fa fa-image"></i>
        Ver fotos
      </a>
    </div>

  </div>


<div class="collapse multi-collapse" id="tel{{$telefono->id}}">
  
  <div class="row p-3 justify-content-center">
    
    <div class="col-4 text-center">
      @if (strlen(Storage::url($telefono->imagen1)) > 9 )
        <img src="{{Storage::url($telefono->imagen1)}}" class="img-fluid" alt="">  
      @else
        <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
      @endif
    </div>

    <div class="col-4 text-center">
      @if (strlen(Storage::url($telefono->imagen2)) > 9 )
        <img src="{{Storage::url($telefono->imagen2)}}" class="img-fluid" alt="">  
      @else
        <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
      @endif
    </div>

    <div class="col-4 text-center">
      @if (strlen(Storage::url($telefono->imagen3)) > 9 )
        <img src="{{Storage::url($telefono->imagen3)}}" class="img-fluid" alt="">  
      @else
        <img src="https://st4.depositphotos.com/14953852/24787/v/450/depositphotos_247872612-stock-illustration-no-image-available-icon-vector.jpg" class="img-fluid" alt="">
      @endif
    </div>
    

    
  </div>

</div>







  @empty
      <li>No hay datos</li>
  @endforelse
  
    {{-- conjunto de datos de el telefono --}}
  
  
  

  
  
  <div class="row p-3 justify-content-center">
    <div class="col-8 text-center">
      

      


    </div>
  </div>

  {{-- <div class="row justify-content-center">
    <div class="col-3 text-center">
      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
      Acepto <a href="#">Terminos y condiciones</a>
    </div>
  </div> --}}

  <div class="row p-3 justify-content-center">


    <div class="col-12 text-center">
      @if($usuario->resguardo_firmado == true)

        <h4> 
          <i class="fa fa-check-circle text-success"></i>
          <b>Aceptado por: </b>   
          {{$usuario->name}}
        </h4>
        
      @else
        
        <div class="row justify-content-center">

          <div class="col-12">
            <h4>
              <i class="fa fa-warning text-danger"></i>
              Aún no es aceptado por el usuario
            </h4>
          </div>
        </div>

        @endif
        
      </div>
    </div>
  </div>
</div>{{--  este es el contenedor de todo --}}







        <!-- Modal -->
        <div class="modal fade" id="msj" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header">
                <h6 class="fw-bold">Por favor comentame tus observaciones</h6>
              </div>
              <div class="modal-body">
                <form action="{{route('observaciones.resguardo')}}" method="POST">
                  @csrf 
                  
                    <textarea class="form-control w-100 h-50" placeholder="Observaciones ..." name="observaciones">{{old('observaciones')}}</textarea>
                    <input type="hidden" name="id" value="{{$usuario->id}}">

              </div>

              <div class="modal-footer">
                <div class="mb-3 mt-2">
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
        </div>
        <!-- Modal -->

    
@endsection