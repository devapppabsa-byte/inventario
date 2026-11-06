@extends('layout')
@section('contenido')
@section('title', 'Empleado con subordinados') 
@include('user.cabecera')


@if (Auth::user()->jefe)
    


<div class="container">
  <div class="row m-1">
      <div class="col-12 h4 text-center">
      {{-- notificacion --}}
              @if (session('acceso_autorizado'))
                  <span class="text-success"> {{session('acceso_autorizado')}} </span>
              @endif
              @if (session('acceso_desautorizado'))
              <span class="text-success"> {{session('acceso_desautorizado')}} </span>
          @endif
      {{-- notificacion --}}
      </div>

  </div>
</div>



<div class="container my-4" id="content">

    <div class="row justify-content-center mt-3">

        <div class="col-sm-12 col-md-12 col-lg-12 scroll-tabla mt-2 bg-white p-5">
            <h3 class="fw-bold">Sitios solicitado por las personas a su cargo </h3>
          @if (session('aut_sitio'))
            <div class="alert alert-success">{!!session('aut_sitio')!!}</div>
          @endif
          @if (session('sitio_desautorizado'))
            <div class="alert alert-secondary">{!!session('sitio_desautorizado')!!}</div>
          @endif
            <div class="tabla-responsive">
            <table class="table table-bordered">

              @if (count($sitios) > 0)
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Usuario</th>
                        <th scope="col">Sitio Web</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Justificación</th>
                        <th scope="col">Autorizar</th>
                    </tr>
                </thead>
              @endif
    

              <tbody>
        
              @forelse ($sitios as $sitio)

                  
              
              <tr>
                    <td data-label="Usuario">{{$sitio->user->name}}</td>
                    <td data-label="Sitio Web">{{$sitio->nombre}}</td>
                    <td data-label="Estado">{{$sitio->status ? 'Autorizado' : 'No autorizado'}}</td>
                    <td data-label="Justificación">{{$sitio->justificacion}}</td>
                    
                    <td data-label="Autorizar" class="col-2">
                      <div class="btn-group">
                        <form action="{{route('autorizar.sitio.jefe', $sitio->id)}}" method="POST">
                          @csrf @method('PATCH')                            
                          <button class="btn btn-sm autoriza {{$sitio->status ? 'btn-secondary' : 'btn-success' }} " {{$sitio->status  ? 'disabled' : 'enable' }}>
                            <i class="fa fa-check"></i>
                          </button>
                        </form>

                        <form action="{{route('desautorizar.sitio.jefe', $sitio->id)}}" method="POST">
                          @csrf @method('PATCH')
                          <button class="btn desautoriza btn-sm {{$sitio->status ? 'btn-danger' : 'btn-secondary' }}" {{!$sitio->status  ? 'disabled' : 'enable' }}>
                            <i class="fa fa-xmark"></i>
                          </button>
                        </form>
                        
                      </div>
                    </td>
                    
                  </tr>


                  @empty
                  
                  <div class="row p-5 justify-content-center">
                    <div class="col-8 text-center mt-4">
                      <i class="fa-solid fa-envelope-open-text fa-5x" ></i> <br>
                      <h4>No hay solicitudes de acceso a sitios web</h4>
                    </div>
                  </div>

                  @endforelse
                
              </tbody>
          </table>
            </div>
        </div>   
        {{-- aqui termina le div de la tabla --}}





        <div class="col-sm-12 col-md-12 col-lg-12 scroll-tabla mt-2 bg-white p-5">
          <h3 class="fw-bold">Software solicitado por las personas a su cargo</h3>
          @if (session('software_autorizado'))
          <div class="alert alert-success">{!!session('software_autorizado')!!}</div>
          @endif
          @if (session('software_desautorizado'))
          <div class="alert alert-secondary">{!!session('software_desautorizado')!!}</div>
          @endif
          <div class="tabla-responsive">
          <table class="table table-bordered">
            @if (count($softwares) > 0 )
              <thead class="table-dark">
                  <tr>
                      <th scope="col">Usuario</th>
                      <th scope="col">Software</th>
                      <th scope="col">Estado</th>
                      <th scope="col">Justificación</th>
                      <th scope="col">Autorizar</th>
                  </tr>
              </thead>
            @endif
            <tbody>
      
            @forelse ($softwares as $software)   
              <tr>
                  <td data-label="Usuario">{{$software->user->name}}</td>
                  <td data-label="Software">{{$software->nombre}}</td>
                  <td data-label="Estado">{{$software->status ? 'Autorizado' : 'No autorizado'}}</td>
                  <td data-label="Justificación">{{$software->justificacion}}</td>
                  
                  <td data-label="Autorizar" class="col-2">
                    <div class="btn-group">
                      <form action="{{route('autorizar.software.jefe', $software->id)}}" method="POST" class="autorisa_software">
                        @csrf @method('PATCH')                            
                        <button class="btn btn-sm autoriza {{$software->status ? 'btn-secondary' : 'btn-success' }}  " {{$software->status  ? 'disabled' : 'enable' }}>
                          <i class="fa fa-check"></i>
                        </button>
                      </form>
                      
                      <form action="{{route('desautorizar.software.jefe', $software->id)}}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm desautoriza {{$software->status ? 'btn-danger' : 'btn-secondary' }} " {{!$software->status  ? 'disabled' : 'enable' }}>
                          <i class="fa fa-xmark"></i>
                        </button>
                      </form>
                      
                    </div>
                  </td>
                  
                </tr>
                
                @empty
                  
                <div class="row p-5 justify-content-center">
                  <div class="col-8 text-center mt-4">
                    <i class="fa-solid fa-envelope-open fa-5x" ></i> <br>
                    <h4>No hay solicitudes de software</h4>
                  </div>
                </div>

                @endforelse
                
            </tbody>
        </table>
          </div>
      </div>  







    </div>


</div>



@else

<div class="container mt-5 bg-white p-5">
  <div class="row mt-4 justify-content-center p-5">
    <div class="col-5 text-center">
      <h4>No hay nada para ti aqui</h4>
      <i class="fa-brands fa-fly fa-5x"></i>
    </div>
  </div>
</div>

@endif




<script>

document.addEventListener('DOMContentLoaded', function(){


  const forms = document.querySelectorAll('form'); //

  forms.forEach(form => {

    //Itera sobre cada formulario
    form.addEventListener('submit', function(e){


      e.preventDefault();

      const url = this.action; //Obtiene la ruta a donde van los datos del formulario
      const formData = new FormData(this);
      const row = this.closest('tr'); //encuentra la fila mas cercana a el formulario
      const csrf = this.querySelector('input[name="_token"]')


      fetch(url, {
        method: 'POST',
        body:formData,
        headers:{
          'X-Request-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN' : csrf,
        }

      })

      .then(response => response.json()) //espera la respuesta json
      .then(data =>{

        if(data.success){
          
          const statusCell = row.querySelector('td:nth-child(3)') //selecciona la celda de 'estado' en la fila
          statusCell.textContent = data.status ? 'Autorizado' : 'No autorizado';

          


          const authorizeButton = row.querySelector('.autoriza'); // encuentra el boton de autorizar
          const desauthorizedButton = row.querySelector('.desautoriza'); //encuentra el boton de desautorizar


          //Si el estado es 'Autorizado'

          // if(data.status){


          //   if(authorizeButton){
          //     //cambia el boton de autorizar a gris y lo deshabilita
          //     authorizeButton.setAttribute('disable', true);
              
          //     authorizeButton.classList.add('btn-secondary');
          //     authorizeButton.classList.remove('btn-success');

          //   }

          //   if(desauthorizedButton){

          //     desauthorizedButton.classList.remove('btn-secondary');
          //     desauthorizedButton.classList.remove('btn-danger');
          //     desauthorizedButton.removeAttribute('disable');

          //   }


          // }


          // else{

          //   if(desauthorizedButton){
              
          //     desauthorizedButton.classList.add('btn-secondary');
          //     desauthorizedButton.classList.remove('btn-danger');
          //     desauthorizedButton.setAttribute('disable', true);

          //   }

          //   if(authorizeButton){

          //     authorizeButton.classList.remove('btn-secondary');
          //     authorizeButton.classList.add('btn-success');
          //     authorizeButton.removeAttribute('disable');


          //   }

          // }


          if(data.status){


            statusCell.classList.add('cascadia', 'fw-bold')


            if(authorizeButton){

                authorizeButton.setAttribute('disabled', true);
                authorizeButton.classList.add('btn-secondary');
                authorizeButton.classList.remove('btn-success');

              
            }

            if(desauthorizedButton){

              desauthorizedButton.classList.add('btn-danger');
              desauthorizedButton.removeAttribute('disabled');
              desauthorizedButton.classList.remove('btn-secondary');
 
            }

          }

          else{

            statusCell.classList.add('cascadia', 'fw-bold')

            if(authorizeButton){

                authorizeButton.removeAttribute('disabled');
                authorizeButton.classList.remove('btn-secondary');
                authorizeButton.classList.add('btn-success');
              
            }

            if(desauthorizedButton){

              desauthorizedButton.classList.remove('btn-danger');
              desauthorizedButton.setAttribute('disabled', true);
              desauthorizedButton.classList.add('btn-secondary');

            }


          }





        }//cierra el if de la respuesta


      });
    });
  });
});


</script>
  







@endsection