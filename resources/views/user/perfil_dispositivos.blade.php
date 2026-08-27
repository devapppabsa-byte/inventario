@extends('layout')
@section('contenido')
@section('title', 'Dispositivos ')
    
@include('user.cabecera')

<div class="container-fluid" id="content">

    
    <div class="row justify-content-center">


        @forelse ($computadoras as $computadora)
            <div class="col-sm-12 col-md-6  col-lg-3 bg-white shadow shadow-sm mx-2 p-5 mt-2">
                <div class="row">
                    <div class="col-12">
                        <h3>Computadora</h3>
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Marca: </b> {{$computadora->marca}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Modelo: </b> {{$computadora->modelo}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>S.O. : </b> {{$computadora->SO}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Tamaño HDD : </b> {{$computadora->size_hdd}} GB </span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Tipo : </b> {{$computadora->tipo}} </span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Tamaño SSD : </b> {{$computadora->size_ssd}} GB </span>
                    </div>
                </div>
            </div>
        @empty
            ...
        @endforelse
        




    

        @forelse ($impresoras as $impresora)            
            <div class="col-sm-12 col-md-6  col-lg-3 bg-white shadow shadow-sm mx-2 p-5 mt-2">
                <div class="row">
                    <div class="col-12">
                        <h3>Impresora</h3>
                        @if ($impresora->comparte != 'Con Nadie')
                            <span><b> Comparte con: </b> <u> {{$impresora->comparte}} </u> </span>
                        @endif
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Marca: </b> {{$impresora->marca}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Modelo: </b> {{$impresora->modelo}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Serie : </b> {{$impresora->serie}} </span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Tipo : </b> {{$impresora->tipo}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Observaciones : </b> {{$impresora->observaciones}}</span>
                    </div>
                </div>
            </div>
        @empty
            ...
        @endforelse
        




        @forelse ($telefonos as $telefono)
            <div class="col-sm-12 col-md-6  col-lg-3 bg-white shadow shadow-sm mx-2 p-5 mt-2">
                <div class="row">
                    <div class="col-12">
                        <h3>Teléfono</h3>
                        @if ($telefono->comparte != 'Con Nadie')
                            <span><b> Comparte con: </b> <u> {{$telefono->comparte}} </u> </span>
                        @endif
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Marca: </b> {{$telefono->marca}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b> Modelo: </b> {{$telefono->modelo}}</span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Serie : </b> {{$telefono->serie}} </span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Tipo : </b> {{$telefono->tipo}} </span>
                    </div>
                    <div class="col-12 m-1">
                        <span><b>Observaciones : </b> {{$telefono->observaciones}}  </span>
                    </div>
                </div>
            </div>

        @empty
            ...
        @endforelse





    </div>



 </div>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.col-sm-12.col-md-5', { opacity: 0, y: 30, scale: 0.95, duration: 0.5, stagger: 0.08, ease: 'power2.out', delay: 0.3 });
  });
</script>
@endsection