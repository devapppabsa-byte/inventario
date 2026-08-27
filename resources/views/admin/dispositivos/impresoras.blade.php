@extends('layout')
@section('contenido')
@section('title', 'Lista de dispositivos')
@include('assets.nav')
@include('assets.nav_dispositivos')






<div class="container ">
    <div class="row p-5 justify-content-center">
        <div class="col-12 bg-white p-5">
          <h4 class="fw-bold mb-3">impresoras</h4>
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
                @forelse ($impresoras as $impresora)
                  <tr>
                    <th>{{$impresora->user->name}}</th>
                    <td>{{$impresora->marca}}</td>
                    <td>{{$impresora->modelo}}</td>
                    <td>{{$impresora->tipo}}</td>
                    <td><a href="{{route('editar.impresora', $impresora->id)}}">Editar</a></td>

                  </tr>
    
                @empty
                    
                @endforelse

                </tbody>
              </table>
        </div>
    </div>
</div>





@endsection