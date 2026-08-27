@extends('layout')
@section('contenido')
@section('title', 'Lista de dispositivos')
@include('assets.nav')
@include('assets.nav_dispositivos')






<div class="container ">
    <div class="row p-5 justify-content-center">
        <div class="col-12 bg-white p-5">
          <h4 class="fw-bold mb-3">Computadoras</h4>
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Responsable</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Modelo</th>
                    <th scope="col">Ram</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Editar</th>
                  </tr>
                </thead>
                <tbody>
                @forelse ($computadoras as $computadora)
                  <tr>
                    <th>{{$computadora->user->name}}</th>
                    <td>{{$computadora->marca}}</td>
                    <td>{{$computadora->modelo}}</td>
                    <td>{{$computadora->ram}} GB</td>
                    <td>{{$computadora->tipo}}</td>
                    <td><a href="{{route('editar.computadora', $computadora->id)}}">Editar</a></td>
                  </tr>
    
                @empty
                    
                @endforelse

                </tbody>
              </table>
        </div>
    </div>
</div>





@endsection