@extends('layout')
@section('title', 'Perfil Consejo Directivo')
@section('contenido')

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-10 text-center bg-white mt-2 p-5 shadow-sm">
                <h1 class="fw-bold">PEDIDOS PENDIENTES</h1>
                <form action="{{route('cerrar.session.admin')}}" method="POST" >
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="fa fa-power-off  me-2"></i>
                        Cerrar Session
                    </button>
                </form>
            </div>
        </div>
    </div>



    <div class="container-fluid mt-5">

        @forelse ($pedidos as $pedido)
        <div class="row justify-content-center mt-5">
            <div class="col-10 bg-white shadow-sm p-5">
                <div class="row h4">
                    <div class="col-3">
                        <b>Nombre: </b> <br>
                        {{$pedido->user->name}}
                    </div>
                    <div class="col-4">
                        <b>Puesto: </b> <br>
                        {{$pedido->user->puesto}}
                    </div>
                    <div class="col-3">
                        <b>Tintas: </b> <br>
                        {{implode(', ' ,json_decode($pedido->colores, true))}}
                    </div>
                    <div class="col-2">
                        <button class="btn btn-dark btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#fo{{$pedido->id}}">
                            <i class="fa fa-eye"></i>
                            Ver Foto
                        </button>
                    </div>
                </div>
            </div>
         </div>
        @empty
            <h1>No hay pedidos pendientes.</h1>
        @endforelse

    </div>





    {{-- aqui va a ir los modales --}}

    @forelse ($pedidos as $pedido)
    <div class="modal fade" id="fo{{$pedido->id}}"  data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <img src="{{str_replace('storage/Public/', 'storage/', Storage::url($pedido->foto_tintas))}}" class="img-fluid" alt="">
          </div>
        </div>
    </div>
    @empty
        
    @endforelse


    

@endsection