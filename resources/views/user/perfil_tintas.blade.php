@extends('layout')
@section('contenido')
@include('user.cabecera')
@section('title', 'Pedido tintas')
    

<div class="container" id="content">
    <div class="row">
        <div class="col-12 text-center">
            @if ($errors->first('checkboxes'))
                <h5 class="text-danger fw-bold">Debe seleccionar almenos un color</h5>
            @endif

            @if ($errors->first('cantidad_cintas'))
                <h5 class="text-danger fw-bold">Debe poner cuantas cintas quiere</h5>
            @endif

            @if ($errors->first('marca'))
                <h5 class="text-danger fw-bold">Debe poner la marca de la impresora</h5>
            @endif  
            
            @if ($errors->first('foto_tintas'))
                <h5 class="text-danger fw-bold">Debe poner la marca de la impresora</h5>
            @endif  



            @if (session('pedido_enviado'))
                <h5 class="text-success text-center fw-bold">              
                  {{session('pedido_enviado')}}
                </h5>
            @endif

            @if (session('anticipado'))
              <li class="text-danger text-center fw-bold h5">              
                  {{session('anticipado')}}
              </li>
            @endif

            @if (session('completado'))
            <li class="text-success text-center fw-bold h5">              
                {{session('completado')}}
            </li>
            @endif
          
        </div>
    </div>


    <div class="row d-flex justify-content-around">
        
        <div class="col-12 mx-1 bg-white p-4 shadow shadow-sm">
            <button class="btn btn-dark btn-sm m-1 fw-bold"  data-bs-toggle="modal" data-bs-target="#pedido">
                <i class="fa fa-plus"></i>
                Realizar pedido
            </button>
            <h3 class="text-center">
                Pedidos de tintas realizados
            </h3>
            <table class="table table-bordered table-responsive-md">
              @if (count($pedidos) > 0 )
                <thead class="table-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Fecha pedido</th>
                        <th scope="col">Fecha entrega</th>
                        <th scope="col">Insumos</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Respuesta</th>
                        <th scope="col">Foto</th>


                    </tr>
                </thead>
              @endif

                <tbody>
                    
                  @forelse ($pedidos as $pedido)


                    @if($pedido->status != "pendiente")

                      <tr>
                        <td>{{$pedido->id}}</td>
                        <td>{{$pedido->numero == 'Otra' ? '' : $pedido->numero}} <b> {{$pedido->marca}} </b> <b> {{$pedido->cantidad}} </b> </td>
                        <td>{{$pedido->fecha_pedido}}</td>
                        <td>{{$pedido->fecha_entrega}}</td>
                        <td>{{ implode(', ', json_decode($pedido->colores, true)) }} </td>
                        <td class="text-start"> <i class="fa fa-check-circle"></i> Completado</td> 
                        <td>{{$pedido->respuesta_admin ? $pedido->respuesta_admin : 'Aún no hay respuesta'}}</td>      
                        <td> <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#fo{{$pedido->id}}" >Foto</button> </td>               
                      </tr>

                    @else
                    
    
                      <tr>
                        <td>{{$pedido->id}}</td>
                        <td>{{$pedido->numero == 'Otra' ? '' : $pedido->numero}} <b> {{$pedido->marca}} </b> <b> {{$pedido->cantidad}} </b> </td>
                        <td>{{$pedido->fecha_pedido}}</td>
                        <td>{{$pedido->fecha_entrega}}</td>
                        <td>{{ implode(', ', json_decode($pedido->colores, true)) }}</td>
                        
                        <td class="text-start">
                          <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              Pendiente
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#c{{$pedido->id}}" style="cursor: pointer">Completo</a>
                              {{-- <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#r{{$pedido->id}}" style="cursor: pointer">Reenviar petición</a> --}}
                            </div>
                          </div>                            
                        </td> 
                        <td>{{$pedido->respuesta_admin ? $pedido->respuesta_admin : 'Aún no hay respuesta'}}</td>
                                              
                        <td> <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#fo{{$pedido->id}}" >Foto</button> </td>     

                      </tr>

                    @endif
                    
    
                    @empty

                      <div class="row justify-content-center p-5">
                        <div class="col-8 text-center mt-5">
                          <i class="fa-solid fa-droplet fa-4x m-3"></i>
                          <h4>No hay pedidos pendientes</h4>
                        </div>
                      </div>

                    @endforelse
          
                </tbody>
            </table>
           <small>Página: {{$pedidos->currentPage()}}</small> 
        </div>



        <div class="col-12 text-center mt-4">
          {{$pedidos->links()}}
        </div>



      </div>
 </div> {{-- ciertre del container que cierra todo --}}








    <!-- Modal -->
    <div class="modal fade" id="pedido" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
              <h5 class="modal-title fw-bold">
                <i class="fa fa-droplet me-2"></i>Nuevo pedido de tintas
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 position-relative">
              <form method="POST" action="{{route('tintas.pedido')}}" enctype="multipart/form-data" id="form_tintas">
                @csrf

                <div class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white rounded"
                     id="overlay_loader" style="z-index: 1050; opacity: .95;">
                  <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                    <div class="spinner-border text-success" style="width: 4rem; height: 4rem;" role="status"></div>
                    <p class="fw-bold mt-3 mb-0">Enviando pedido...</p>
                  </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Número de impresora</label>
                    <select name="numero" class="form-select" id="tipo" value="{{old('numero')}}">
                        <option value="544">544</option>
                        <option value="504">504</option>
                        <option value="664">664</option>
                        <option value="Otra">Mi impresora es de otra marca y/o otro tipo</option>
                        <option value="Cintas">Cintas para impresora de bascula</option>
                    </select>
                </div>

                <div class="mb-4 d-none" id="cantidad_cintas">
                  <label class="form-label fw-bold">Cantidad</label>
                  <input type="number" name="cantidad_cintas" min="0" max="2" class="form-control">
                </div>

                <div class="mb-4 d-none" id="marca">
                  <label class="form-label fw-bold">Marca de impresora</label>
                  <input type="text" name="marca" placeholder="Ej: HP W1102W toner" id="marca_input" min="0" class="form-control">
                </div>

                <div class="mb-4" id="colores">
                    <label class="form-label fw-bold d-block">Colores a reemplazar</label>
                    <div class="d-flex justify-content-center gap-3">
                        <label class="color-swatch text-center" for="negro">
                            <input type="checkbox" value="Negro" id="negro" name="checkboxes[]" class="d-none">
                            <div class="swatch-circle bg-dark"></div>
                            <small class="fw-bold">BK</small>
                        </label>

                        <label class="color-swatch text-center" for="rosa">
                            <input type="checkbox" value="Rosa" id="rosa" name="checkboxes[]" class="d-none">
                            <div class="swatch-circle" style="background-color: #e91e90"></div>
                            <small class="fw-bold" style="color: #e91e90">M</small>
                        </label>

                        <label class="color-swatch text-center" for="azul">
                            <input type="checkbox" value="Azul" id="azul" name="checkboxes[]" class="d-none">
                            <div class="swatch-circle" style="background-color: #0032fc"></div>
                            <small class="fw-bold" style="color: #0032fc">C</small>
                        </label>

                        <label class="color-swatch text-center" for="amarillo">
                            <input type="checkbox" value="Amarillo" id="amarillo" name="checkboxes[]" class="d-none">
                            <div class="swatch-circle" style="background-color: #aaaa28"></div>
                            <small class="fw-bold" style="color: #aaaa28">Y</small>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Foto del nivel de tinta</label>
                    <input type="file" accept="image/*" name="foto_tintas" class="form-control" id="foto_tintas" required>
                    <div class="col-8 mx-auto mt-2" id="contenedor_foto"></div>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-success flex-fill fw-bold" id="confirmar">
                    <i class="fa fa-check me-1"></i> Confirmar
                  </button>
                  <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal -->





      @forelse ($pedidos as $pedido)
                  <!-- Modal -->
        <div class="modal fade" id="r{{$pedido->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-body">
                <h4>¿Reenviar el pedido?</h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success">
                  <i class="fa fa-paper-plane"></i>
                  Reenviar
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal -->



        {{-- modal que contiene la foto --}}
        <!-- Modal -->
        <div class="modal fade" id="fo{{$pedido->id}}"  data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <img src="{{str_replace('storage/Public/', 'storage/', Storage::url($pedido->foto_tintas))}}" class="img-fluid" alt="">
            </div>
          </div>
        </div>
        <!-- Modal -->



        <!-- Modal -->
        <div class="modal fade" id="c{{$pedido->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-body">
                <h4>¿Se completo el pedido?</h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                <form action="{{route('pedido.completo', $pedido->id)}}" method="POST">
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
      @empty
          
      @endforelse


{{-- aqui esta el html de mi menu contextual --}}
<div class="menu-contextual" id="menuContextual">
  <ul>
      <li>
        <a data-bs-toggle="modal" data-bs-target="#pedido">
          <i class="fa fa-plus"></i>
            Realizar Pedido
        </a>
      </li>
  </ul>
</div>





{{-- //aqui van a ir los scripts --}}
<script>

    const select_tipo = document.getElementById('tipo').addEventListener('change', function(){
    const valor_select = this.value;
    const colores = document.getElementById('colores');
    const cantidad_cintas = document.getElementById('cantidad_cintas');
    const marca_impresora = document.getElementById('marca');


    if(valor_select === 'Cintas'){

      colores.classList.add('d-none');
      cantidad_cintas.classList.remove('d-none');
      marca_impresora.classList.add('d-none');

    }

    else{

      colores.classList.remove('d-none');

    }
    

    if(valor_select === "Otra"){
      marca_impresora.classList.remove('d-none');
      cantidad_cintas.classList.add('d-none');
    }

    if(valor_select != "Otra" && valor_select != "Cintas"){
      marca_impresora.classList.add('d-none');
      cantidad_cintas.classList.add('d-none');
    }

  });

</script>



<script>
  const formTintas = document.getElementById('form_tintas')
  const confirmar = document.getElementById('confirmar')
  const overlayLoader = document.getElementById('overlay_loader')
  formTintas.addEventListener('submit', function(){
    overlayLoader.classList.remove('d-none')
    confirmar.disabled = true
  })





</script>




{{-- //este hace que la imagen del pedido tnga una vista previa --}}
<script>

  document.getElementById('foto_tintas').addEventListener("change", function(event){
    const contenedor = document.getElementById('contenedor_foto');
    
    contenedor.innerHTML = "";
    const archivo = event.target.files[0];

    if(archivo){
      
      const reader = new FileReader();
      reader.onload = function(e){
        const img = document.createElement("img");
        img.src = e.target.result;
        img.classList.add("img-fluid", "mt-2");
        img.style.maxWidht = "100%";
        img.style.border = "2px solid #ccc";
        img.style.borderRadius= "8px";
        contenedor.appendChild(img);
      }

      reader.readAsDataURL(archivo);

    }



  });

</script>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.bg-white.p-4', { opacity: 0, y: 30, duration: 0.6, ease: 'power2.out', delay: 0.3 });
    gsap.from('table tbody tr', { opacity: 0, x: -20, duration: 0.4, stagger: 0.05, ease: 'power2.out', delay: 0.5 });
  });
</script>
@endsection