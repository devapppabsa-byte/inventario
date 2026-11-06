@extends('layout')
@section('contenido')
@section('title', 'Lista de dispositivos')
@include('assets.nav')
@include('assets.nav_dispositivos')






<div class="container ">
    <div class="row p-5 justify-content-center">
        <div class="col-12 bg-white p-5">
          <h4 class="fw-bold mb-3">Telefonos</h4>
            <div class="tabla-responsive">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Responsable</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Modelo</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Editar</th>

                  </tr>
                </thead>
                <tbody>
                @forelse ($telefonos as $telefono)
                  <tr>
                    <th data-label="Responsable">{{$telefono->user->name}}</th>
                    <td data-label="Marca">{{$telefono->marca}}</td>
                    <td data-label="Modelo">{{$telefono->modelo}}</td>
                    <td data-label="Tipo">{{$telefono->tipo}}</td>
                    <td data-label="Editar"><a href="{{route('editar.telefono', $telefono->id)}}">Editar</a></td>

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