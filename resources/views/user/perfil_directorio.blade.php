@extends('layout')
@section('title', 'Directorio')
@section('contenido')
@include('user.cabecera')    

<div class="container-fluid" id="content">

    <div class="row mb-2 my-4 justify-content-center">
        <div class="col-sm-12 col-md-9 col-lg-6 bg-white shadow shadow-sm p-3 text-center">
                <h5 class="fw-bold">Buscar en el directorio: </h5>
                <input type="search" id="buscador" class="form-control cascadia fw-bold negro" placeholder="Buscar por:  nombre, extensión, correo, celular, puesto o planta" autofocus >
        </div>
    </div>


    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 justify-content-center" id="contenedor" >
            @forelse ($usuarios as $usuario)
            <div class="col search">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if ($usuario->planta == 'Planta 1')
                                    <i class="fa-solid fa-user-circle fa-3x text-primary"></i>
                                @elseif ($usuario->planta == 'Planta 2')
                                    <i class="fa-solid fa-user-circle fa-3x text-success"></i>
                                @else
                                    <i class="fa-solid fa-user-circle fa-3x text-warning"></i>
                                @endif
                            </div>
                            <div>
                                <h5 class="card-title fw-bold mb-0">{{$usuario->name}}</h5>
                                <small class="text-muted">{{$usuario->puesto}}</small>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1">
                                <a href="mailto:{{$usuario->correo}}" class="text-decoration-none text-dark">
                                    <i class="fa fa-envelope me-2 text-muted"></i>{{$usuario->correo}}
                                </a>
                            </li>
                            @if ($usuario->celular)
                            <li class="mb-1">
                                <a href="tel:{{$usuario->celular}}" class="text-decoration-none text-dark">
                                    <i class="fa fa-mobile me-2 text-muted"></i>{{$usuario->celular}}
                                </a>
                            </li>
                            @endif
                            <li class="mb-1">
                                <i class="fa fa-phone me-2 text-muted"></i>Ext. {{$usuario->extension}}
                            </li>
                            <li>
                                <i class="fa fa-building me-2 text-muted"></i>{{$usuario->planta}}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-solid fa-users fa-4x mb-3"></i>
                    <h5>No hay usuarios en el directorio</h5>
                </div>
            @endforelse
                
                
    </div>

</div>





<script>
    //aqui esta el codigo para realizar la busqueda en  tiempo real
    const searchInput = document.getElementById('buscador');
    const contenedor = document.getElementById('contenedor');
    const userDivs = contenedor.getElementsByClassName('col');

    searchInput.addEventListener('keyup', function(){

        const filtro = searchInput.value.toLowerCase();

        Array.from(userDivs).forEach(function(userDiv){

            const userText = userDiv.innerText.toLowerCase();

            if (userText.indexOf(filtro) > -1) {
                userDiv.style.display = "";



            } else {
                userDiv.style.display = "none";
            }

        })

    });




</script>





@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('#contenedor .col', { opacity: 0, y: 30, scale: 0.95, duration: 0.5, stagger: 0.06, ease: 'power2.out', delay: 0.3 });
  });
</script>
@endsection