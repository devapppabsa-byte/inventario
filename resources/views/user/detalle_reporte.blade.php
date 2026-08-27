@php
use Carbon\Carbon;
@endphp 
    
@extends('layout')
@section('contenido')
@section('title', 'Detalle reporte')
@include('user.cabecera')

<div class="container-fluid mt-4" id="content">
    <div class="row justify-content-center">
        <div class="col-10 bg-white shadow">

            @if ($reporte->status == 'pendiente')
                <div class="row">
                    <div class="col-12 bg-dark p-3 text-center text-white">
                        <h2>Ticket #{{$reporte->id}}</h2>
                        <a class="text-white fw-bold" href="{{route('tickets.show')}}">Volver</a>
                        @if (session('comentado'))
                            <small class="text-success">{{session('comentado')}}</small>
                        @endif
                    </div>
                </div>
            @endif

            @if ($reporte->status == 'completado')
                <div class="row">
                    <div class="col-12 bg-success p-3 text-center text-white">
                        <h2>Ticket #{{$reporte->id}}</h2>
                        <a class="text-white fw-bold" href="{{route('tickets.show')}}">Volver</a>
                    </div>
                </div>
            @endif
            

            <div class="row p-2">

                <div class="col-5 p-3">
                    <div class="row">
                        <div class="col-12 m-2">
                            <b>Dispositivo: </b> <br>
                            <span>{{$reporte->dispositivo}}</span>
                        </div>


                        
                        @if ($reporte->dispositivo == 'Otro')
                        <div class="col-12 m-2">
                            <b>Falla en: </b> <br>
                            <i>{{$reporte->otro}}</i>
                        </div>
                        @endif
    
                        <div class="col-12 m-2">
                            <b>Descripción: </b> <br>
                            <i>{{$reporte->descripcion}}.</i>
                        </div>
    
                        <div class="col-12 m-2">
                            <b>Estado: </b> <br>
                            <i>{{$reporte->status}}</i>
                        </div>

                        <div class="col-12 m-2">
                            <b>Prioridad: </b> <br>
                            <i>{{$reporte->prioridad}}</i>
                        </div>
                        <div class="col-12 mt-5">
                            <img src="{{route('reporte.imagen', $reporte->id)}}"  data-bs-toggle="modal" data-bs-target="#img{{$reporte->id}}" class="img-fluid zoom" alt="">
                        </div>


                    </div>
                </div>


                <div class="col-7 cascadia bg-light p-4 reportes-scroll border-0">
                    <h3>Seguimiento: </h3>

                    <div class="row">
                        @forelse ($comentarios as $comentario)
                            <div class="col-12 my-4">
                                <b class="font-size-18">{{($comentario->usuario == Auth::user()->name) ? 'Yo' : $comentario->usuario }}: </b>
                                <p class="mb-0">{{$comentario->comentario}}</p>
                                <small class="fw-bold bd-highlight">{{Carbon::parse($comentario->created_at)->diffForHumans()}}</small>
                            </div>
                        @empty
                            <li>Sin comentarios aún</li>
                        @endforelse


                        @if ($reporte->status != 'completado')
                            <div class="col-12 mt-5 ">
                                <form action="{{route('comentario.reporte.usuario', $reporte->id)}}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <i class="fa fa-comment"></i>
                                        <b>{{Auth::user()->name}}: </b>
                                        <textarea name="comentario" class="form-control w-100 h-25" autofocus></textarea>
                                        @error('comentario')
                                           <h6 class="mt-3 text-danger"> {{ $message}} </h6>
                                        @enderror
                                    </div>
                                    <div class="from-group">
                                        <button class="btn btn-dark btn-sm">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>





{{-- aqui esta el modal --}}
  <!-- Modal -->
<div class="modal fade" id="img{{$reporte->id}}"  data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <img src="{{route('reporte.imagen', $reporte->id)}}" class="img-fluid" alt="">
          </div>
    </div>
</div>


    
@endsection