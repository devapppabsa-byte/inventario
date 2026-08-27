@extends('layout')
@section('contenido')
@include('user.cabecera') 
@section('title', 'Reportes de fallas')


{{-- 
<div class="container">
  <iframe src="https://www.milenio.com/" width="100%" height="700px"></iframe>
</div> --}}
<style>
  select.color-select option.green {
    background-color: #d4edda;
    color: #155724;
  }

  select.color-select option.red {
    background-color: #f8d7da;
    color: #721c24;
  }

  select.color-select option.yellow {
    background-color: #fff3cd;
    color: #856404;
  }
</style>

<div class="container" id="content">

    <div class="row">
        <div class="col-12 text-center">
            @if (session('reportado'))
                <h5 class="fw-bold text-center text-success">
                  {{session('reportado')}}
                </h5>
            @endif
            @if (session('completado'))
                <h5 class="fw-bold text-center text-success">
                  {{session('completado')}}
                </h5>
            @endif
        </div>
    </div>


    <div class="row d-flex justify-content-around">
        
        <div class="col-12 mx-1 bg-white p-4 shadow shadow-sm">
            <button class="btn btn-dark btn-sm m-1 fw-bold" data-bs-toggle="modal" data-bs-target="#reporte">
                <i class="fa fa-plus"></i>
                Realizar reporte
            </button>

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <h3 class="text-center">
              Reportes realizados
            </h3>
            <table class="table table-bordered table-responsive-md" style="transition: 3s">
              @if (count($reportes) > 0)
                <thead class="table-dark ">
                  <tr>
                      <th scope="col">ID Ticket</th>
                      <th scope="col">Lo que falla</th>
                      <th scope="col">Fecha de reporte</th>
                      <th scope="col">Detalles</th>
                      <th scope="col">Estado</th>
                      <th scope="col">Ver Seguimiento</th>

                  </tr>
                </thead>
              @endif
                <tbody>
                   
                @forelse ($reportes as $reporte)
                  @if ($reporte->status == 'pendiente')
                      <tr>
                        <td class="text-center">{{$reporte->id}}
                        <td>
                          {{$reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo}}
                        </td>
                        <td><small>{{$reporte->fecha_reporte}}</small></td>
                        <td>{{$reporte->descripcion}}</td>

                        <td class="text-start">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Pendiente
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#c{{$reporte->id}}" style="cursor: pointer">Completo</a>
                                    {{-- <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#r{{$reporte->id}}" style="cursor: pointer">Reenviar petición</a> --}}
                                </div>
                            </div>                            
                        </td>   
                        <td class="text-center">
                          <a href="{{route('detalle.reporte', $reporte->id)}}" class="btn btn-dark btn-sm">
                            <i class="fa fa-eye"></i> 
                          </a>
                        </td>   
                      </tr>
                  @else
                      <tr>
                        <td class="text-center">{{$reporte->id}}
                        <td>
                          <a href="{{route('detalle.reporte', $reporte->id)}}">
                            {{$reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo}}
                          </a>
                        </td>
                        <td><small>{{$reporte->fecha_reporte}}</small></td>
                        <td>{{$reporte->descripcion}}</td>
                        <td class="text-start"> <i class="fa fa-check-circle"></i> Resuelto </td>                      
                      </tr>     
                      
                  @endif
                
                @empty
         
                <div class="row justify-content-center p-5">
                  <div class="col-8 text-center mt-5">
                    <i class="fa-solid fa-pencil fa-4x m-3"></i>
                    <h4>No hay reportes aqui</h4>
                  </div>
                </div>
                @endforelse



             
                    
                    
                </tbody>
            </table>
            <small>Página: {{$reportes->currentPage()}}</small> 
            <div class="row m-2">
              <div class="col-12 text-center bg-white">
                {{$reportes->links()}}
              </div>
            </div>

        </div>
    </div>

 </div> {{-- ciertre del container que cierra todo --}}




@forelse ($reportes as $reporte)
    

<!-- Modal -->
<div class="modal fade" id="c{{$reporte->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-body">
          <h4>¿Se completo el soporte? </h4>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <form action="{{route('reporte.completo', $reporte->id)}}" method="POST">
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
  <div class="modal fade" id="r{{$reporte->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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




      
@empty
@endforelse
      



          <!-- Modal -->
          <div class="modal fade" id="reporte" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-0">
                  <h5 class="modal-title fw-bold"><i class="fa fa-laptop-medical me-2"></i>Nuevo reporte</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                  <form action="{{route('reporte.post')}}" id="reporte_send" enctype="multipart/form-data" method="post">
                    @csrf @method('post')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción de la falla</label>
                            <textarea type="text" id="descripcion" name="descripcion" class="form-control" required>{{old('descripcion')}}</textarea>
                    </div>
                      <div class="mb-3">
                        <input type="hidden" id="cc_email" name="cc_email" value="{{Auth()->user()->correo}}">
                        <input type="hidden" name="nombre_usuario" value="{{Auth()->user()->name}}" id="nombre_usuario">
                        <input type="hidden" id="to_email" name="to_email" value="arturo.resendiz@grupopabsa.com">
                        <label class="form-label fw-bold">Dispositivo que fallo</label>
      
                        <select name="dispositivo" id="dispositivo" class="form-control">
                          <option value="computadora">💻​ Computadora</option>
                          <option value="impresora">🖨️​ Impresora</option>
                          <option value="Otro">💣​ Otro</option>
                        </select>
      
                      </div>
      
      
                      <div class="mb-3 d-none"  id="otra_falla">
                        <label class="form-label fw-bold">¿Que es lo que falla?</label>
                        <input type="text" class="form-control" id="otro" name="otro">
                      </div>
      
      
      
                      <div class="mb-3">

                        <label class="form-label fw-bold">Prioridad</label>
      
                        <select name="prioridad" id="prioridad" class="form-control">
                          <option class="green"  value="Baja">🟢 Baja</option>
                          <option class="yellow"  value="Media">🟡 Media</option>
                          <option class="red" value="Alta">🔴 Alta</option>
                        </select>
      
                      </div>
      
                      <div class="mb-3">
                        <label class="form-label fw-bold">Foto o captura del problema</label>
                        <input type="file" name="imagen" class="form-control" id="falla_picture" accept="image/*" required >
                        <div class="col-12 text-center p-4" id="container_falla_picture">
      
                        </div>
                      </div>
      
      
              
      
                </div>
      
                <div class="modal-footer">
                  <button class="btn btn-dark" id="enviar_reporte">
                    <i class="fa fa-check me-1"></i>
                    Confirmar
                  </button>
                </form>
                <button type="button" id="cerrar_formulario_ticket" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
          <!-- Modal -->



      {{-- aqui esta el html de mi menu contextual --}}
<div class="menu-contextual" id="menuContextual">
  <ul>
      <li>
        <a data-bs-toggle="modal" data-bs-target="#reporte">
          <i class="fa fa-plus"></i>
            Realizar Reporte
        </a>
      </li>
  </ul>
</div>


{{-- aqui van los scripts de esta seccion --}}

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>


<script>



document.getElementById('dispositivo').addEventListener('change', function(){
  
  const otro = document.getElementById('otra_falla');

  if(this.value === 'Otro'){
    otro.classList.remove('d-none');
    otro.classList.add('d-block');
  }
  else{
    otro.classList.add('d-none');
    otro.classList.remove('d-block');
  }
})

</script>

<script>
  //aqui va el JS de la vista previa de la imagen
  // Escuchamos el evento 'change' del input de tipo file
  document.getElementById('falla_picture').addEventListener('change', function (e) {


    const file = e.target.files[0];
    const previewContainer = document.getElementById('container_falla_picture');
    previewContainer.innerHTML = '';
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.classList.add('img-fluid', 'rounded');
        img.style.maxHeight = '300px';
        previewContainer.appendChild(img);
      };
      reader.readAsDataURL(file);
    } else {
      previewContainer.innerHTML = '<p class="text-danger">El archivo no es una imagen válida.</p>';
    }
  });
</script>




{{-- Aqui va le codigo para enviar el Email --}}



<script>

(function(){
    emailjs.init("mvA2hTi9RX5iDG6Ry"); // tu public key de EmailJS
})();


const form = document.getElementById('reporte_send');
const imagen = document.getElementById('falla_picture');
const enviar_reporte = document.getElementById('enviar_reporte');
const cerrar = document.getElementById('cerrar_formulario_ticket');


document.getElementById('reporte_send').addEventListener('submit', function(e){

  imagen.disabled = true;
  enviar_reporte.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Enviando...'
  enviar_reporte.disabled=true;
  cerrar.disabled=true
 


  e.preventDefault();
  emailjs.sendForm('service_qcc7wza', 'template_9mc060d', form)
    .then(function(response){
      imagen.disabled = false;

      
      form.submit();

    }, function(error){

      form.submit();
    }
  )
});
form.addEventListener('submit', handleSubmit);






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
    
