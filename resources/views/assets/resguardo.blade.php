@extends('layout')
@section('contenido')
@section('title', 'Resguardo')

@include('user.cabecera')


<div class="container">
  <div class="row justify-content-center">

    <div class="col-12 text-center fw-bold p-3">
      @if (session('aceptado'))
         <h4 class="text-success"> {{session('aceptado')}} </h4>
      @endif
      @error('observaciones')
         <h4 class="text-danger"> 
          <i class="fa fa-warning"></i>
          {{$errors->first('observaciones')}} 
        </h4>
      @enderror
      @if (session('observaciones'))
      <h4 class="text-success"> {{session('observaciones')}} </h4>
   @endif
    </div>

  </div>
</div>
<div class="container bg-white p-3 pt-5 border shadow">{{--  este es el contenedor de todo --}}


  
  <!-- encabezado -->
  <div class="container px-3 px-md-5 py-2">
      <div class="row border border-5">
        
        <div class="col-sm-12 col-md-4  col-lg-4 centrar-verticalmente p-2 mt-2 text-center">
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

      <div class="row justify-content-center">
        <div class="col-3 p-2 text-center">
          <h4 class="fw-bold ">
            Resguardo de Dispositivos
          </h4>

          @if (session('fotos_subidas'))
            <div class="alert alert-success text-center alert-sm border border-bold h5 ">
              <i class="fa fa-check-circle mx-3"></i>
            {{session('fotos_subidas')}}
            </div>
          @endif


          @foreach ($errors->all() as $error)
            <h4 class="text-danger">{{$error}}</h4>
          @endforeach



        </div>
      </div>

  {{-- conjunto de datos del usuario --}}
      <div class="row px-2">
        <div class="col-12 text-center" style="background-color: #e9ecef">
          <h5 class="mt-2 fw-bold">Datos del Usuario</h5>
        </div>
      </div>


      <div class="row p-0 border-bottom text-center">

        <div class="col-4 p-3 font-size-18 ">
          <b class="p-0 m-0">Nombre: </b> 
          <span>{{$usuario->name}}</span>
        </div>

        <div class="col-4 p-3 font-size-18 ">
          <b class="p-0 m-0">Puesto: </b> 
          <span>{{$usuario->puesto}}</span>
        </div>

        <div class="col-4 p-3 font-size-18 ">
          <b class="p-0 m-0"> Planta: </b> 
          <span>{{$usuario->planta}}</span>
        </div>

      </div>
  {{-- conjunto de datos del usuario --}}




  {{-- conjunto de dartos del equipo de computo --}}
  @forelse ($computadoras as $computadora)

      <div class="row mt-4 px-2 border-top border-bottom">
        <div class="col-9 text-center" style="background-color: #f5f5f5">
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



      <div class="row p-3 border-bottom  mb-2 ">

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

        <div class="col-4 p-1 font-size-18">
          <b class="p-0 m-0">Procesador: </b> 
          <span> {{$computadora->procesador}}</span>
        </div>


        <div class="col-12 p-1 font-size-18">
          <b class="p-0 m-0">Accesorios: </b> 
          <span>{{$computadora->accesorios}}</span>
        </div>

        <div class="col-12 p-1 font-size-18">
          <b class="p-0 m-0">Observaciones: </b> 
          <span>{{$computadora->observaciones}}</span>
        </div>


        <div class="col-12 text-center p-1 font-size-18">
          <a  data-bs-toggle="collapse" href="#com{{$computadora->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" 
            aria-controls="multiCollapseExample1">
            <i class="fa fa-image"></i>
            Ver fotos
          </a>
        </div>

      </div>

      <div class="collapse multi-collapse" id="com{{$computadora->id}}">

        <form action="{{route('fotos.computadora.usuario', $computadora->id)}}" method="POST" enctype="multipart/form-data">
          @csrf @method('PATCH')

          <div class="row p-3 justify-content-center" >
        
            <div class="col-4 text-center">
              @if (!empty($computadora->imagen1) && Storage::exists($computadora->imagen1))
                <img src="{{Storage::url($computadora->imagen1)}}" class="img-fluid" alt="">
              @else
                <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
                <input type="file" name="imagen1" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
              @endif
            </div>

            <div class="col-4 text-center">
              @if (!empty($computadora->imagen2) && Storage::exists($computadora->imagen2))
                <img src="{{Storage::url($computadora->imagen2)}}" class="img-fluid" alt="">
              @else
                <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
                <input type="file" name="imagen2" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
              @endif
            </div>

            <div class="col-4">
              @if (!empty($computadora->imagen3) && Storage::exists($computadora->imagen3))
                <img src="{{Storage::url($computadora->imagen3)}}" class="img-fluid" alt="">
              @else
                <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
                <input type="file" name="imagen3" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
              @endif
            </div>

          </div>

      
          @if (empty($computadora->imagen1)  || empty($computadora->imagen2) || empty($computadora->imagen3))

            <div class="row justify-content-center">
              <div class="col-4 text-center">
                  <button class="btn btn-success btn-sm">
                    <i class="fa fa-upload mx-2"></i>
                    Cargar imagenes de la computadora
                  </button>
              </div>
            </div>
          @endif

      </form>



      </div>



    @empty
      <li>No hay datos de Computadoras</li>
    @endforelse
  {{-- conjunto de dartos del equipo de computo --}}


















  {{-- conjunto de dartos de la impresora --}}
  @forelse ($impresoras as $impresora)
      
    <div class="row mt-4 px-2 border-top border-bottom">
      <div class="col-9 text-center" style="background-color: #e9ecef">
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

        <div class="col-12 p-1 font-size-18">
          <b class="p-0 m-0">Observaciones: </b> 
          <span class="font-size-18">{{$impresora->observaciones}}</span>
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
        <form action="{{route('fotos.impresora.usuario', $impresora->id)}}" method="POST" enctype="multipart/form-data">
          @csrf @method('PATCH')
            <div class="row p-3 justify-content-around">
          
              <div class="col-4 text-center">
                @if (!empty($impresora->imagen1) && Storage::exists($impresora->imagen1))
                <img src="{{Storage::url($impresora->imagen1)}}" class="img-fluid" alt="">
                @else
                <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
                <input type="file" name="imagen1" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
                @endif
              </div>
              
              <div class="col-4 text-center">
                @if (!empty($impresora->imagen2) && Storage::exists($impresora->imagen2))
                <img src="{{Storage::url($impresora->imagen2)}}" class="img-fluid" alt="">
                @else
                <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
                <input type="file" name="imagen2" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
                @endif
              </div>
          
            </div>

          @if (empty($impresora->imagen1)  || empty($impresora->imagen2))
            <div class="row justify-content-center">
              <div class="col-4 text-center">
                  <button class="btn btn-success btn-sm">
                    <i class="fa fa-upload mx-2"></i>
                    Cargar imagenes de la impresora
                  </button>
              </div>
            </div>
          @endif


        </form>

      </div>

      






    @empty
        <li>No hay datos de impresoras</li>
    @endforelse
    {{-- cionjunto de datos de la impresora --}}




  {{-- conjunto de datos de el telefono --}}
  @forelse ($telefonos as $telefono)
      
  <div class="row mt-4 px-2 border-top border-bottom">
    <div class="col-9 text-center" style="background-color: #f5f5f5">
      <h5 class="mt-2 fw-bold">Datos del Equipo de el Teléfono</h5>
    </div>
    <div class="col-3 mt-2">
      <h5>  ¿Equipo Nuevo?<b>
        @if ($telefono->estado)
          <i class="fa fa-check-circle mx-1 text-success"></i> Si               
        @else
          <i class="fa fa-xmark mx-1 text-danger"></i> No               
          
        @endif
        
        
        </b></h5>
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

    <div class="col-12 p-1 font-size-18">
      <b class="p-0 m-0">Observaciones: </b> 
      <span class="font-size-18">{{$telefono->observaciones}}</span>
    </div>

    <div class="col-12 text-center p-1 font-size-18">
      <a  data-bs-toggle="collapse" href="#tel{{$telefono->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" 
        aria-controls="multiCollapseExample1">
        <i class="fa fa-image"></i>
        Ver fotos
      </a>
    </div>

  </div>


  <form action="{{route('fotos.telefono.usuario', $telefono->id)}}" method="POST" enctype="multipart/form-data">
    
    @csrf @method('PATCH')
    <div class="collapse multi-collapse" id="tel{{$telefono->id}}">
      
      <div class="row justify-content-center p-3">
        
        <div class="col-4">
          
          @if (!empty($telefono->imagen1) && Storage::exists($telefono->imagen1))
            <img src="{{Storage::url($telefono->imagen1)}}" class="img-fluid" alt="">

          @else
            <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
            <input type="file" name="imagen1" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
          @endif
        </div>
        


        <div class="col-4">
          @if (!empty($telefono->imagen2) && Storage::exists($telefono->imagen2))
            <img src="{{Storage::url($telefono->imagen2)}}" class="img-fluid" alt="">
          @else
            <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
            <input type="file" name="imagen2" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
          @endif
        </div>
      


        <div class="col-4">
          @if (!empty($telefono->imagen3) && Storage::exists($telefono->imagen3))
          <img src="{{Storage::url($telefono->imagen3)}}" class="img-fluid" alt="">
          @else
            <img src="https://static.thenounproject.com/png/11204-200.png" class="img-fluid" alt="">
            <input type="file" name="imagen3" class="form-control" accept=".jpg, .jpeg, .png, .gif, .svg, .webp" required>
          @endif
        </div>

      </div>

      
    @if (empty($telefono->imagen1)  || empty($telefono->imagen2) || empty($telefono->imagen3))
      <div class="row justify-content-center">
        <div class="col-4 text-center">
            <button class="btn btn-success btn-sm">
              <i class="fa fa-upload mx-2"></i>
              Cargar imagenes del telefono
            </button>
        </div>
      </div>
    @endif



    </div>

  </form>





  @empty
      <li>No hay datos de telefonos</li>
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


          @if (count($telefonos) == 0 && count($impresoras) == 0 && count($computadoras) ==0 )

          <div class="col-6">
            <h4 class="fw-bold">Aún no hay dispositivos para aceptar el resguardo</h4>
          </div>
          
          @else

          @if($usuario->resguardo_firmado == true)

            <div class="col-12 text-center">

              <div class="row">
                <div class="col-12">
                  <h4> 
                    <i class="fa fa-check-circle text-success"></i>
                    <b>Aceptado por: </b>   
                    {{$usuario->name}}
                  </h4>

                </div>
              </div>
           @else
            <div class="row justify-content-center">

              <div class="col-12 text-center">
                <h4>
                  <i class="fa fa-warning text-danger"></i>
                  Aún no es aceptado por el usuario
                </h4>
              </div>
              <div class="col-6">
                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#msj">
                  <i class="fa fa-xmark"></i>
                  Rechazar
                </button>
              </div>
  
              <div class="col-6">
                <form action="{{route('confirma.resguardo', $usuario->id)}}" onsubmit="return confirma()" method="POST">
                  @csrf
                    <button class="btn btn-success w-100">
                      <i class="fa fa-check"></i>
                      Aceptar
                    </button>
                                 
                  
                  @endif

                </form>
              </div>

            </div>

        @endif


      
          </div>
        </div>

    
        
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

        <!-- Modal -->
        <div class="modal fade" id="notificacion" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header p-1">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                  <i class="fa fa-xmark"></i>
                </button>
              </div>
              <div class="modal-body text-center">
               
                <h4>
                  Por favor cargue todas las imagenes <br> <br>
                  <i class="fa fa-upload fa-3x"></i>
                </h4>

              </div>
            </div>
          </div>
        </div>
        <!-- Modal -->





        <script>


          function confirma(){
            return confirm('¿Confirmar que aceptas el Resguardo?')
          }


          
            
            function mostrarVistPrevia(input, previewElement){

              if(input.files && input.files[0] ){

                const reader = new FileReader();

                //este evento se dispara cuando el archivo ha sido leido
                reader.onload = function(e){
                  
                  //se obtiene la url de la imagen
                  previewElement.src = e.target.result;

                  }

                  //inica la lectura del archivo como una URL 

                  reader.readAsDataURL(input.files[0]);
                }
              }


        
        
        //selecciona todos los inputs del tipo  'file' del documento
        document.querySelectorAll('input[type="file"]').forEach(input => {

          //encuentra el elemento de imagen justo antes del input (en el DOM seria el hermano anterior)
          const imgPreview = input.previousElementSibling; //Toma la etiqueta IMG que esta cerca del input

          //se agrega un listener

          input.addEventListener('change', function(){
            mostrarVistPrevia(this, imgPreview)
          })


          
        });









            

          



        </script>
    
@endsection


