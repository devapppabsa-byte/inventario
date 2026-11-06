@extends('layout')
@section('contenido')
@section('title', 'Lista de dispositivos')
@include('assets.nav')
@include('assets.nav_dispositivos')






<div class="container ">
    <div class="row p-5 justify-content-center">
        <div class="col-12 bg-white p-5">
          <h4 class="fw-bold mb-3">Computadoras</h4>
            <div class="tabla-responsive">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Responsable</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Modelo</th>
                    <th scope="col">Procesador</th>
                    <th scope="col-2">Ram</th>
                    <th scope="col">Editar</th>

                  </tr>
                </thead>
                <tbody>
                @forelse ($computadoras as $computadora)
                  <tr>
                    <th data-label="Responsable">{{$computadora->user->name}}</th>
                    <td data-label="Marca">{{$computadora->marca}}</td>
                    <td data-label="Modelo">{{$computadora->modelo}}</td>
                    <td data-label="Procesador">{{$computadora->procesador}}</td>
                    <td data-label="Ram">{{$computadora->ram}} GB</td>
                    <td data-label="Editar"><a href="{{route('editar.computadora', $computadora->id)}}">Editar</a></td>

                  </tr>
    
                @empty
                    
                @endforelse

                </tbody>
              </table>
            </div>
        </div>
    </div>
</div>





@endsection