@extends('layout')
@section('contenido')
@section('title', 'Control de Accesos')
@include('assets.nav')


<div class="container">
    <div class="row m-1">
        <div class="col-12 text-center fw-bold p-3">
            <a href="{{route('agregar.usuarios')}}">
              <i class="fa fa-arrow-left" ></i>
              Volver
            </a>
        </div>

        <div class="col-12 h4 text-center">
        {{-- notificacion --}}
                @if (session('solicitado'))
                    <span class="text-success"> {{session('solicitado')}} </span>
                @endif
        {{-- notificacion --}}
        </div>

    </div>
</div>

    
<div class="container bg-white  p-2 ">

  <!-- encabezado -->
  <div class="container px-5 py-2 m-3 ">
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
            <b>Fecha: </b> <span> {{substr($user->created_at, 0, 10)}} </span>
        </div>

        <div class="col-4 text-center">
            <b>Área de trabajo: </b> <span>{{$user->puesto}}</span>
        </div>

        <div class="col-4 text-center">
            <b>Titular: </b> <span>{{$user->name}}</span>
        </div>

    </div>





    <div class="row justify-content-center mt-5">
        <div class="col-11 ">
            <h4>Software requerido</h4>
            @if (session('software_autorizado'))
                <h5 class="text-center text-success fw-bold">{{session('software_autorizado')}}</h5>
            @endif
            @if (session('software_desautorizado'))
            <h5 class="text-center text-danger fw-bold">{{session('software_desautorizado')}}</h5>
        @endif
            <table class="table border">
              @if (!empty($software[0]))

                <thead class="table-dark">
                  <tr>
                    <th scope="col-2">Software</th>
                    <th scope="col-6">Justificación</th>
                    <th scope="col-2">Estado</th>
                    <th scope="col-2">Autorizar</th>

                  </tr>
                </thead>
                  
              @endif
                <tbody>
                    @forelse ($software as $soft)

                    <tr>
                      <th class="col-2" >{{$soft->nombre}}</th>
                      <td class="col-6"> {{$soft->justificacion}}</td>
                      <td class="col-2"> {{$soft->status  ? 'Autorizado' : 'No Autorizado' }}</td>
                      <td class="col-2">
                        <div class="btn-group">
                          <form action="{{route('autorizar.software', $soft->id)}}" method="POST">
                            @csrf @method('PATCH')                            
                            <button class="btn btn-success btn-sm" {{$soft->status  ? 'disabled' : 'enable' }}>
                              <i class="fa fa-check"></i>
                            </button>
                          </form>

                          <form action="{{route('desautorizar.software', $soft->id)}}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-danger btn-sm" {{!$soft->status  ? 'disabled' : 'enable' }}>
                              <i class="fa fa-xmark"></i>
                            </button>
                          </form>

                        </div>
                      </td>
                    </tr>
                        
                    @empty
                        <li>No hay datos</li>
                    @endforelse
  
                </tbody>
              </table>
        </div>
    </div>






    <div class="row justify-content-center mt-5 mb-5">
        <div class="col-11 ">
            <h4>Acceso a la web requerido</h4>
            @if (session('sitio_autorizado'))
                <h5 class="text-success text-center">
                  {{session('sitio_autorizado')}}
                </h5>
            @endif
            @if (session('sitio_desautorizado'))
            <h5 class="text-danger text-center">
              {{session('sitio_desautorizado')}}
            </h5>
        @endif
            <table class="table border">
              @if (!empty($sitios[0]))

                <thead class="table-dark">
                  <tr>
                    <th scope="col-2">Sitio</th>
                    <th scope="col-6">Justificación</th>
                    <th scope="col-2">Estado</th>
                    <th scope="col-2">Autorizar</th>
                  </tr>
                </thead>
                  
              @endif

                <tbody>
                
                @forelse ($sitios as $sitio)
                    <tr>
                        <th class="col-2">{{$sitio->nombre}}</th>
                        <td class="col-6">{{$sitio->justificacion}}</td>
                        <td class="col-2">{{$sitio->status ? 'Autorizado' : 'No Autorizado'}}</td>
                        <td class="col-2">
                          <div class="btn-group">
                            <form action="{{route('autorizar.sitio', $sitio->id)}}" method="POST">
                              @csrf @method('PATCH')
                              <button class="btn btn-success btn-sm" {{$sitio->status  ? 'disabled' : 'enable' }}>
                                <i class="fa fa-check"></i>
                              </button>
                            </form>
                            <form action="{{route('desautorizar.sitio', $sitio->id)}}" method="POST">
                              @csrf @method('PATCH')
                            <button class="btn btn-danger btn-sm" {{!$sitio->status  ? 'disabled' : 'enable' }}>
                              <i class="fa fa-xmark"></i>
                            </button>
                            </form>
  
                          </div>
                        </td>
                    </tr>
                @empty
                    <li>No hay datos</li>
                @endforelse                 

                </tbody>
              </table>
        </div>
    </div>    



{{-- ESTO ES EL GRUPO DE BOTONOS QUE DICEN SI ACEPTA EL USUARIO, JEFE DIRECTO Y SISTEMAS --}}
    {{-- <div class="row justify-content-center mt-4">
        <div class="col-3 text-center">

            <button class="btn btn-secondary">
                Acepta Encargado de Sistemas
            </button>
            <br>
            <i class="fa fa-warning text-danger fa-2x mt-2"></i>
            <h5>Aún no se acepta por el Encargado de Sistemas</h5>

        </div>
        <div class="col-3 text-center">

            <button class="btn btn-secondary">
                Acepta Titular el Equipo
            </button>
            <br>
            <i class="fa fa-warning text-danger fa-2x mt-2"></i>
            <h5>Aún no se acepta por el Titular el Equipo</h5>

        </div>
        <div class="col-3 text-center">

            <button class="btn btn-secondary">
                Acepta Jefe Directo
            </button>
            <br>            
            <i class="fa fa-warning text-danger fa-2x mt-2"></i>
            <h5>Aún no se acepta por el Acepta Jefe Directo</h5>

        </div>
    </div> --}}



  </div> {{-- cuerpo de el formato --}}













</div>




@endsection