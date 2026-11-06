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

            @if ($errors->first('entrega'))
                <h5 class="text-danger fw-bold">{{$errors->first('entrega')}} Vuelve a abrir Completar e inténtalo de nuevo.</h5>
            @endif

            @if ($errors->first('pendientes'))
                <h5 class="text-danger fw-bold">{{$errors->first('pendientes')}} Vuelve a abrir Completar e inténtalo de nuevo.</h5>
            @endif

            @if ($errors->first('cantidad_pendiente'))
                <h5 class="text-danger fw-bold">{{$errors->first('cantidad_pendiente')}} Vuelve a abrir Completar e inténtalo de nuevo.</h5>
            @endif  



            @if (session('pedido_enviado'))
                <h5 class="text-success text-center fw-bold">              
                  {{session('pedido_enviado')}}
                </h5>
            @endif

            @if (session('reenviado'))
                <h5 class="text-success text-center fw-bold">
                  {{session('reenviado')}}
                </h5>
            @endif

            @if (session('correo_fallido'))
                <div class="alert alert-warning">
                  <p class="mb-0">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    Tu pedido quedó guardado, pero el aviso por correo no se pudo entregar.
                    Usa «Reenviar aviso» en la tabla para intentarlo de nuevo.
                  </p>
                </div>
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
        
        <div class="col-12 mx-0 mx-md-1 bg-white p-4 shadow shadow-sm">
            <button class="btn btn-dark btn-sm m-1 fw-bold"  data-bs-toggle="modal" data-bs-target="#pedido">
                <i class="fa fa-plus"></i>
                Realizar pedido
            </button>
            <h3 class="text-center">
                Pedidos de tintas realizados
            </h3>
            <div class="tabla-responsive">
            <table class="table table-bordered">
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
                        <th scope="col">Reenviar aviso</th>


                    </tr>
                </thead>
              @endif

                <tbody>
                    
                  @forelse ($pedidos as $pedido)


                    @if($pedido->status != "pendiente")

                      <tr>
                        <td data-label="ID">{{$pedido->id}}</td>
                        <td data-label="Tipo">{{$pedido->numero == 'Otra' ? '' : $pedido->numero}} <b> {{$pedido->marca}} </b> <b> {{$pedido->cantidad}} </b> </td>
                        <td data-label="Fecha pedido">{{$pedido->fecha_pedido}}</td>
                        <td data-label="Fecha entrega">{{$pedido->fecha_entrega}}</td>
                        <td data-label="Insumos">{{ implode(', ', json_decode($pedido->colores, true)) }} </td>
                        <td data-label="Estado" class="text-start">
                          @if (strcasecmp($pedido->status, 'Entrega parcial') === 0)
                            <i class="fa fa-triangle-exclamation"></i> Entrega parcial
                            @if ($pedido->hijos->isNotEmpty())
                              <small class="d-block text-muted">Faltan: {{ implode(', ', json_decode($pedido->hijos->first()->colores, true) ?: []) }}</small>
                            @endif
                          @else
                            <i class="fa fa-check-circle"></i> Completado
                            @if ($pedido->hijos->isNotEmpty())
                              <small class="d-block text-muted">Quedaron: {{ implode(', ', json_decode($pedido->hijos->last()->colores, true) ?: []) }}</small>
                            @endif
                          @endif
                        </td>
                        <td data-label="Respuesta">{{$pedido->respuesta_admin ? $pedido->respuesta_admin : 'Aún no hay respuesta'}}</td>
                        <td data-label="Foto"> <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#fo{{$pedido->id}}" >Foto</button> </td>
                        <td data-label="Reenviar aviso" class="{{in_array($pedido->id, $reenviables ?? []) ? '' : 'celda-vacia'}}">
                          @if (in_array($pedido->id, $reenviables ?? []))
                            <button class="btn btn-outline-danger btn-sm fw-bold boton-reenviar" data-bs-toggle="modal" data-bs-target="#r{{$pedido->id}}">
                              <i class="fa fa-paper-plane"></i> Reenviar aviso
                            </button>
                          @endif
                        </td>
                      </tr>

                    @else
                    
    
                      <tr>
                        <td data-label="ID">{{$pedido->id}}</td>
                        <td data-label="Tipo">{{$pedido->numero == 'Otra' ? '' : $pedido->numero}} <b> {{$pedido->marca}} </b> <b> {{$pedido->cantidad}} </b> </td>
                        <td data-label="Fecha pedido">{{$pedido->fecha_pedido}}</td>
                        <td data-label="Fecha entrega">{{$pedido->fecha_entrega}}</td>
                        <td data-label="Insumos">{{ implode(', ', json_decode($pedido->colores, true)) }}</td>
                        
                        <td data-label="Estado" class="text-start">
                          <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              Pendiente
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#c{{$pedido->id}}" style="cursor: pointer">Completo</a>
                            </div>
                          </div>
                          @if ($pedido->parent_id)
                            <small class="d-block text-muted">Continuación de #{{$pedido->parent_id}}</small>
                          @endif
                        </td> 
                        <td data-label="Respuesta">{{$pedido->respuesta_admin ? $pedido->respuesta_admin : 'Aún no hay respuesta'}}</td>
                        <td data-label="Foto"> <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#fo{{$pedido->id}}" >Foto</button> </td>
                        <td data-label="Reenviar aviso" class="{{in_array($pedido->id, $reenviables ?? []) ? '' : 'celda-vacia'}}">
                          @if (in_array($pedido->id, $reenviables ?? []))
                            <button class="btn btn-outline-danger btn-sm fw-bold boton-reenviar" data-bs-toggle="modal" data-bs-target="#r{{$pedido->id}}">
                              <i class="fa fa-paper-plane"></i> Reenviar aviso
                            </button>
                          @endif
                        </td>

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
            </div>
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
            <div class="modal-body p-4">
              <form method="POST" action="{{route('tintas.pedido')}}" enctype="multipart/form-data" id="form_tintas">
                @csrf

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
                            <input type="checkbox" value="Negro" id="negro" name="checkboxes[]" class="visually-hidden">
                            <div class="swatch-circle bg-dark"></div>
                            <small class="fw-bold">BK</small>
                        </label>

                        <label class="color-swatch text-center" for="rosa">
                            <input type="checkbox" value="Rosa" id="rosa" name="checkboxes[]" class="visually-hidden">
                            <div class="swatch-circle" style="background-color: #e91e90"></div>
                            <small class="fw-bold" style="color: #e91e90">M</small>
                        </label>

                        <label class="color-swatch text-center" for="azul">
                            <input type="checkbox" value="Azul" id="azul" name="checkboxes[]" class="visually-hidden">
                            <div class="swatch-circle" style="background-color: #0032fc"></div>
                            <small class="fw-bold" style="color: #0032fc">C</small>
                        </label>

                        <label class="color-swatch text-center" for="amarillo">
                            <input type="checkbox" value="Amarillo" id="amarillo" name="checkboxes[]" class="visually-hidden">
                            <div class="swatch-circle" style="background-color: #aaaa28"></div>
                            <small class="fw-bold" style="color: #aaaa28">Y</small>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Foto del nivel de tinta</label>
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" name="foto_tintas" class="form-control" id="foto_tintas" required>
                    <div class="border rounded p-3">
                        <dl class="row mb-0">
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Formatos</dt>
                            <dd class="col-8 col-sm-9">Imágenes JPG, PNG, GIF o WEBP</dd>
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Peso</dt>
                            <dd class="col-8 col-sm-9">Hasta {{ $maxFotoMb }} MB</dd>
                        </dl>
                    </div>

                    <div class="form-text mt-2">Las fotos demasiado grandes se reducen automáticamente a 1600 px
                        antes de enviarse, para que no tengas que ajustarlas tú.</div>
                    @error('foto_tintas')
                        <small class="text-danger fw-bold">{{$message}}</small>
                    @enderror
                    <div class="col-8 mx-auto mt-2" id="contenedor_foto"></div>
                </div>

                <div class="d-flex gap-2">
                  {{-- data-loader-bloquea: el watchdog de loader-modal.js los
                       rehabilita si la peticion se queda colgada. --}}
                  <button class="btn btn-success flex-fill fw-bold" id="confirmar" data-loader-bloquea>
                    <i class="fa fa-check me-1"></i> Confirmar
                  </button>
                  <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal" data-loader-bloquea>Cerrar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal -->





      @forelse ($pedidos as $pedido)
                  <!-- Modal -->
        @if (in_array($pedido->id, $reenviables ?? []))
        <div class="modal fade" id="r{{$pedido->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-body">
                <h4>¿Reenviar el aviso del pedido?</h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form action="{{route('tintas.reenvio', $pedido->id)}}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-paper-plane"></i>
                    Reenviar
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @endif
        <!-- Modal -->



        {{-- modal que contiene la foto --}}
        <!-- Modal -->
        <div class="modal fade" id="fo{{$pedido->id}}"  data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <img src="{{Storage::url($pedido->foto_tintas)}}" class="img-fluid" alt="">
            </div>
          </div>
        </div>
        <!-- Modal -->


        {{-- <div class="modal fade" id="#" data-keyboard="false" tabindex="-1" arial-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <img src="{{Storage::url($somethin->something)}}" class="img-fluid" alt="">
            </div>
          </div>
        </div> --}}


        <!-- Modal -->
        <div class="modal fade" id="c{{$pedido->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <form id="form-c{{$pedido->id}}" action="{{route('pedido.completo', $pedido->id)}}" method="POST">
                @csrf @method('PATCH')
              <div class="modal-body text-center">
                <h4>¿Se entregaron todas las tintas?</h4>
                <p class="text-muted small mb-0">Si faltaron, se genera un pedido residual solo con lo que falta.</p>
                <div id="pend-{{$pedido->id}}" class="text-start mt-3 d-none">
                  @if ($pedido->numero === 'Cintas')
                    @php $totalCintas = (int) (json_decode($pedido->colores, true)[0] ?? 0); @endphp
                    <label class="form-label fw-bold">Cintas pendientes (de {{ $totalCintas }})</label>
                    <input type="number" name="cantidad_pendiente" class="form-control" min="1" max="{{ max(1, $totalCintas) }}">
                  @else
                    <label class="form-label fw-bold">Marca las tintas que FALTAN</label>
                    @foreach (json_decode($pedido->colores, true) ?: [] as $color)
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pendientes[]" value="{{ $color }}" id="u{{$pedido->id}}-{{ $loop->index }}">
                        <label class="form-check-label" for="u{{$pedido->id}}-{{ $loop->index }}">{{ $color }}</label>
                      </div>
                    @endforeach
                  @endif
                  <div id="aviso-{{$pedido->id}}" class="text-danger small mt-2 d-none"></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                  <button type="submit" name="entrega" value="total" class="btn btn-success">
                    <i class="fa fa-check"></i>
                    Sí, todas
                  </button>
                  <button type="button" id="btn-parcial-{{$pedido->id}}" class="btn btn-warning" onclick="marcarParcial({{$pedido->id}})">
                    <i class="fa fa-triangle-exclamation"></i>
                    No, faltaron
                  </button>
                  <button type="submit" name="entrega" value="parcial" id="btn-generar-{{$pedido->id}}" class="btn btn-warning d-none" onclick="return validarParcial({{$pedido->id}})">
                    Marcar parcial y crear residual
                  </button>


              </div>
              </form>
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

function marcarParcial(id){
    const pend = document.getElementById('pend-'+id);
    if (!pend || !pend.classList.contains('d-none')) return;
    pend.classList.remove('d-none');
    const parcial = document.getElementById('btn-parcial-'+id);
    if (parcial) parcial.classList.add('d-none');
    const generar = document.getElementById('btn-generar-'+id);
    if (generar) generar.classList.remove('d-none');
}

function validarParcial(id){
    const modal = document.getElementById('c'+id);
    const aviso = document.getElementById('aviso-'+id);
    let error = '';
    const cintas = modal ? modal.querySelector('input[name="cantidad_pendiente"]') : null;
    if (cintas) {
        const total = parseInt(cintas.max, 10) || 0;
        const v = parseInt(cintas.value, 10);
        if (cintas.value === '') error = 'Indica cuantas cintas quedaron pendientes.';
        else if (isNaN(v) || v < 1) error = 'Deben quedar al menos 1 cinta pendiente.';
        else if (v > total) error = 'No pueden quedar mas cintas de las pedidas (' + total + ').';
        else if (v >= total) error = 'Si faltan todas las cintas el pedido no se entrego: dejalo como pendiente.';
    } else if (modal) {
        const todas = modal.querySelectorAll('input[name="pendientes[]"]');
        const marcadas = modal.querySelectorAll('input[name="pendientes[]"]:checked');
        if (marcadas.length === 0) error = 'Marca las tintas que quedaron pendientes.';
        else if (marcadas.length >= todas.length) error = 'Si faltan todas las tintas el pedido no se entrego: dejalo como pendiente.';
    }
    if (error) {
        if (aviso) {
            aviso.textContent = error;
            aviso.classList.remove('d-none');
        }
        return false;
    }
    if (aviso) aviso.classList.add('d-none');
    return true;
}

</script>



<script>
  const formTintas = document.getElementById('form_tintas')
  const modalPedido = document.getElementById('pedido')

  // El overlay lo inyecta loader-modal.js dentro de .modal-content, asi que
  // tapa header, body y footer en vez de solo el body. Antes era un div fijo
  // aqui adentro y por eso el encabezado y el pie seguian visibles.
  formTintas.addEventListener('submit', function(){
    ModalLoader.mostrar(modalPedido, 'Enviando pedido...')
  })





</script>




{{-- //este hace que la imagen del pedido tnga una vista previa --}}
<script>

  document.getElementById('foto_tintas').addEventListener("change", function(event){
    const contenedor = document.getElementById('contenedor_foto');
    const input = event.target;

    contenedor.innerHTML = '<div class="text-muted small">Reduciendo la foto...</div>';

    const original = input.files && input.files[0];

    if(!original){
      return;
    }

    // Vercel corta con 413 cualquier cuerpo mayor a 4.5 MB, asi que la foto se
    // reescala antes de salir del navegador. El FileList del input se reemplaza
    // con DataTransfer para que siga siendo un POST normal.
    CompresorImagen.procesar(input).then(function(){
      const archivo = input.files && input.files[0];

      if(!archivo){
        contenedor.innerHTML = '';
        return;
      }

      contenedor.innerHTML = "";

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

      if(archivo.size < original.size){
        const nota = document.createElement('div');
        nota.className = 'text-muted small';
        nota.textContent = 'Foto reducida de ' + (original.size / 1048576).toFixed(1) + ' MB a '
          + Math.max(1, Math.round(archivo.size / 1024)) + ' KB.';
        contenedor.appendChild(nota);
      }
    }).catch(function(){
      // Si algo falla se manda la foto como esta; que decida el servidor.
      contenedor.innerHTML = "";
    });
  });

</script>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.bg-white.p-4', { opacity: 0, y: 30, duration: 0.6, ease: 'power2.out', delay: 0.3 });
    gsap.from('table tbody tr', { opacity: 0, duration: 0.4, stagger: 0.05, ease: 'power2.out', delay: 0.5 });
  });
</script>
@endsection