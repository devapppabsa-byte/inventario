@extends('layout')
@section('title', 'Control de Accesos')
@section('contenido')
@include('user.cabecera')
@php
    use Carbon\Carbon;
@endphp

<div class="container">
    <div class="row m-1">
        <div class="col-12 h4 text-center">
        {{-- notificacion --}}
                @if (session('solicitado'))
                    <span class="text-success"> {{session('solicitado')}} </span>
                @endif
        {{-- notificacion --}}
        </div>

    </div>


    <div class="row">
      <div class="col-12">
        @if (session('acceso_eliminado'))
           <h4 class="text-center text-danger fw-bold">
              {{session('acceso_eliminado')}}
           </h4>
        @endif
      </div>
    </div>

</div>

    
<div class="container bg-white  p-2" id="content">

  <!-- encabezado -->
  <div class="container px-3 px-md-5 py-2 m-0 m-md-3">
    <div class="row border border-5 justify-content-center">
      
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
              SOLICITUD DE ALTA / MODIFICACÓN DE ACCESOS A USUARIOS DE TI
            </span>
          </div>
        </div>
      </div>
  
      <div class=" col-sm-12 col-md-4 col-lg-4 border mt-2">
        <div class="row">
          <div class="col-sm-6 col-md-4 col-lg-4 border fw-bold text-center">Código</div>
          <div class="col-sm-6 col-md-8 col-lg-8 border p-0 text-center" >FO/GP/SIS/005/01</div>
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



  

  <div class="container mt-5 p-3"> {{-- cuerpo de el formato --}}
    
    <div class="row font-size-18 justify-content-center">

        <div class="col-4 text-center">
            <b>Fecha: </b> <span> {{Carbon::parse(Auth::user()->created_at)->translatedFormat('l, d F Y');}} </span>
        </div>

        <div class="col-4 text-center">
            <b>Área de trabajo: </b> <span>{{{Auth::user()->puesto}}}</span>
        </div>

        <div class="col-4 text-center">
            <b>Titular: </b> <span>{{Auth::user()->name}}</span>
        </div>

    </div>




    <div class="row justify-content-center mt-5">
        <div class="col-11 ">
            <h4>Software requerido</h4>
            <div class="tabla-responsive">
            <table class="table border" id="userTable">
                <thead class="table-dark">
                  <tr>
                    <th scope="col-2">Software</th>
                    <th scope="col-6">Justificación</th>
                    <th scope="col-2">Estado</th>
                    <th scope="col-2">Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                    @forelse ($software as $soft)

                    <tr>
                      <th data-label="Software" class="col-2">{{$soft->nombre}}</th>
                      <td data-label="Justificación" class="col-6"> {{$soft->justificacion}} </td>
                      <td data-label="Estado" class="col-2"> {{$soft->status ? 'Autorizado' : 'No autorizado' }} </td>
                      <td data-label="Eliminar" class="col-2">
                        <form action="{{route('eliminar.acceso', $soft->id)}}" method="POST">
                          @csrf @method('DELETE')
                          <button class="btn btn-dark btn-sm">
                            <i class="fa fa-trash"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                        
                    @empty
                        <li>No hay datos</li>
                    @endforelse

                  <tr>

                    <th class="sin-etiqueta" scope="row d-flex items-align-center">
                        <form id="software" action="{{route('peticion.software')}}" method="POST">
                            @csrf
                            <div class="mb-3 ">
                                <label for="">Software: </label>
                                <input type="text" placeholder="Aspel SAE" name="software" value="{{old('software')}}" class="form-control form-control-sm mt-3 suave"> <br>
                                {!!$errors->first('software', '<small class="text-danger"> :message </small>')!!}
                            </div>
                        </th>

                        <td class="sin-etiqueta">
                            <div class="mb-3 ">
                                <label for="" class="fw-bold">Justificación: </label>
                                <input type="text" name="justificacion_software" placeholder="Necesito Aspel SAE por que trabajo con los registros de la empresa" class="form-control w-100 h-25 suave mt-2" value="{{old('justificacion_software')}}">
                                {!!$errors->first('justificacion_software', '<small class="text-danger"> :message </small>')!!}<br>
                              </div>
                            </td>
                            
                        <td class="sin-etiqueta">
                              
                              <button class="btn btn-dark btn-sm w-100 mt-5" id="button1">Solicitar</button>
                          </form>
                            <div id="notification" class="alert alert-success d-none" role="alert">
                              ¡Solicitud enviada correctamente!
                            </div>
                        </td>
                  </tr>
  
                </tbody>
              </table>
            </div>
        </div>
    </div>






    <div class="row justify-content-center mt-5 mb-5">
        <div class="col-11 ">
            <h4>Acceso a la web requerido</h4>
            <div class="tabla-responsive">
            <table class="table border" id="sitioTable">
                <thead class="table-dark">
                  <tr>
                    <th scope="col-2">Sitio</th>
                    <th scope="col-6">Justificación</th>
                    <th scope="col-2">Estado</th>
                    <th scope="col-2">Eliminar</th>

                  </tr>
                </thead>
                <tbody>
                
                @forelse ($sitios as $sitio)
                    <tr>
                        <th data-label="Sitio" class="col-2">{{$sitio->nombre}} </th>
                        <td data-label="Justificación" class="col-6">{{$sitio->justificacion}}</td>
                        <td data-label="Estado" class="col-2">{{$sitio->status ? 'Autorizado'  :  'No Autorizado'}}</td>
                        <td data-label="Eliminar" class="col-2">
                          <form action="{{route('eliminar.acceso', $sitio->id)}}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-dark btn-sm">
                              <i class="fa fa-trash"></i>
                            </button>
                          </form>

                        </td>
                    </tr>
                @empty
                    
                @endforelse


                  <th class="sin-etiqueta" scope="row d-flex items-align-center">
                    <form action="{{route('peticion.sitio')}}" id="sitio" method="POST">
                        @csrf
                        <div class="mb-3 ">
                            <label for="">Sitio: </label>
                            <input type="text" placeholder="DOF (Diario Oficial de la Federación)" name="sitio" value="{{old('sitio')}}" class="form-control form-control-sm mt-3 suave"> <br>
                            {!!$errors->first('sitio', '<small class="text-danger"> :message </small>')!!}
                        </div>
                  </th>
                    
                      <td class="sin-etiqueta">
                        <div class="mb-3 ">
                            <label for="" class="fw-bold">Justificación: </label>
                            <input name="justificacion_sitio" placeholder="Consulto diariamente este sitio saber el tipo de cambio" class="form-control w-100 mt-2 h-25 suave" value="{{old('justificacion_sitio')}}""> 
                            {!!$errors->first('justificacion_sitio', '<small class="text-danger"> :message </small>')!!}<br>
                          </div>
                        </td>
                        
                        <td class="sin-etiqueta">
                          
                          <button class="btn btn-dark btn-sm mt-5 w-100" id="button2">Solicitar</button>
                        </form>
                      </td>


                </tbody>
              </table>
            </div>
        </div>
    </div>    

  </div> {{-- cuerpo de el formato --}}



</div>













{{-- //aqui va a ir el script para lo del scroll --}}

<script>
  {
    // Escuchar el evento de envío del formulario
    document.getElementById('software').addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar el envío tradicional del formulario

        var form = e.target; // Obtener el formulario
        var formData = new FormData(form); // Obtener los datos del formulario

        // Limpiar mensajes de error anteriores
        document.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

        // Crear un nuevo objeto XMLHttpRequest para la solicitud AJAX
        var xhr = new XMLHttpRequest();
        
        // Abrir la conexión a la ruta que maneja la solicitud
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}'); // Incluir el token CSRF

        // Manejar la respuesta de la solicitud
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.success) {
                    // Crear una nueva fila <tr> con los datos del software y justificación
                    var newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <th class="col-2">${response.software.nombre}</th>
                        <td class="col-8">${response.software.justificacion}</td>
                        <td class="col-2">${response.software.status ? 'Autorizado' : 'No autorizado'}</td>
                        <td class="col-2"> <button class="btn btn-dark btn-sm" onclick="alert('recarga la pagina')"> <i class="fa fa-trash"></i> </button> </td>
                    `;

                    // Obtener la fila que contiene el formulario
                    var formRow = document.getElementById('software').closest('tr');
                    var userTable = document.querySelector('#userTable tbody');

                    // Insertar la nueva fila antes de la fila del formulario
                    if (userTable.contains(formRow)) {
                        userTable.insertBefore(newRow, formRow);
                    }

                    // Limpiar el formulario
                    form.reset();
                }
            } else if (xhr.status === 422) {
                // Si hay errores de validación
                var response = JSON.parse(xhr.responseText);

                if (!response.success && response.errors) {
                    // Mostrar los errores de validación
                    for (var field in response.errors) {
                        var errorMessage = response.errors[field][0];
                        var errorField = document.querySelector(`[name="${field}"]`);
                        var errorElement = document.createElement('small');
                        errorElement.classList.add('text-danger');
                        errorElement.textContent = errorMessage;
                        
                        // Insertar el mensaje de error debajo del campo
                        errorField.parentNode.appendChild(errorElement);
                    }
                }
            } else {
                console.error('Error en la solicitud AJAX');
            }
        };

        // Manejar errores de red
        xhr.onerror = function() {
            console.error('Error de red');
        };

        // Enviar los datos del formulario
        xhr.send(formData);
    });

  }


  

      {

        document.getElementById('sitio').addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar el envío tradicional del formulario

        var form = e.target; // Obtener el formulario
        var formData = new FormData(form); // Obtener los datos del formulario

        // Limpiar mensajes de error anteriores
        document.querySelectorAll('#sitio .text-danger').forEach(el => el.textContent = '');

        // Crear un nuevo objeto XMLHttpRequest para la solicitud AJAX
        var xhr = new XMLHttpRequest();
        
        // Abrir la conexión a la ruta que maneja la solicitud
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}'); // Incluir el token CSRF

        // Manejar la respuesta de la solicitud
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.success) {
                    // Crear una nueva fila <tr> con los datos del sitio y justificación
                    var newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <th class="col-2">${response.sitio.nombre}</th>
                        <td class="col-8">${response.sitio.justificacion}</td>
                        <td class="col-2">${response.sitio.status ? 'Autorizado' : 'No Autorizado'}</td>
                        <td class="col-2">
                        
                            <button class="btn btn-dark btn-sm" disabled>
                              <i class="fa fa-trash"></i>
                            </button>
                          
                        </td>
                    `;

                    // Obtener la fila que contiene el formulario
                    var formRow = document.getElementById('sitio').closest('tr');
                    var sitioTable = document.querySelector('#sitioTable tbody');

                    // Insertar la nueva fila antes de la fila del formulario
                    sitioTable.insertBefore(newRow, formRow);

                    // Limpiar el formulario
                    form.reset();
                }
            } else if (xhr.status === 422) {
                // Si hay errores de validación
                var response = JSON.parse(xhr.responseText);

                if (!response.success && response.errors) {
                    // Mostrar los errores de validación
                    for (var field in response.errors) {
                        var errorMessage = response.errors[field][0];
                        var errorField = document.querySelector(`[name="${field}"]`);
                        var errorElement = document.createElement('small');
                        errorElement.classList.add('text-danger');
                        errorElement.textContent = errorMessage;
                        
                        // Insertar el mensaje de error debajo del campo
                        errorField.parentNode.appendChild(errorElement);
                    }
                }
            } else {
                console.error('Error en la solicitud AJAX');
            }
        };

        // Manejar errores de red
        xhr.onerror = function() {
            console.error('Error de red');
        };

        // Enviar los datos del formulario
        xhr.send(formData);
    });

  }

</script>



@endsection