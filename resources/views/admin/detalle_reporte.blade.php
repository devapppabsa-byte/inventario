@php
use Carbon\Carbon;
@endphp 
    
@extends('layout')
@section('contenido')
@section('title', 'Detalle reporte')
@include($navView ?? 'assets.nav')

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-9 bg-white shadow">

            @if ($reporte->status == 'pendiente')
                <div class="row">
                    <div class="col-12 bg-dark p-3 text-center text-white">
                        <h2>Ticket #{{$reporte->id}}</h2> <br>
                        <a class="text-white fw-bold" href="{{route($rutaVolver ?? 'admin.soporte')}}">Volver</a> <br>
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
                        <a class="text-white fw-bold" href="{{route($rutaVolver ?? 'admin.soporte')}}">Volver</a>
                    </div>
                </div>
            @endif
            

            <div class="row p-2">

                <div class="col-6 p-3">
                    <div class="row">
                        <div class="col-12 m-2">
                            <b>Usuario: </b>
                            <span>{{$reporte->user->name ?? 'Usuario eliminado'}}</span>
                            @if ($reporte->user)
                                @if ($reporte->user->puesto)
                                    <small class="text-muted d-block">{{$reporte->user->puesto}}
                                        @if ($reporte->user->planta) — Planta {{$reporte->user->planta}} @endif
                                    </small>
                                @endif
                                <small class="text-muted d-block">
                                    @if ($reporte->user->extension) <i class="fa fa-phone me-1"></i>Ext: {{$reporte->user->extension}} @endif
                                    @if ($reporte->user->celular) <i class="fa fa-mobile me-1"></i>{{$reporte->user->celular}} @endif
                                    @if ($reporte->user->correo) <i class="fa fa-envelope me-1"></i>{{$reporte->user->correo}} @endif
                                </small>
                            @endif
                        </div>

                        <div class="col-12 m-2">
                            <b>Dispositivo: </b>
                            <span>{{$reporte->dispositivo}}</span>
                        </div>


                        
                        @if ($reporte->dispositivo == 'Otro')
                        <div class="col-12 m-2">
                            <b>Falla en: </b> 
                            <span>{{$reporte->otro}}</span>
                        </div>
                        @endif
    
                        <div class="col-12 m-2">
                            <b>Descripción: </b>
                            @include('partials._descripcion_enriquecida', ['descripcion' => $reporte->descripcion])
                        </div>
    
                        <div class="col-12 m-2">
                            <b>Estado: </b> 
                            <span>{{$reporte->status}}</span>
                        </div>

                        <div class="col-12 m-2">
                            <b>Prioridad: </b> 
                            <span>{{$reporte->prioridad}}</span>
                        </div>

                        <div class="col-12 m-2">
                            <b>Fecha de solución: </b>
                            <span>{{($reporte->fecha_solucion && $reporte->fecha_solucion != 'pendiente') ? $reporte->fecha_solucion : '—'}}</span>
                        </div>

                        <div class="col-12 m-2">
                            <b>Calificación: </b>
                            @if ($reporte->calificacion)
                                <span class="text-warning" title="Calificación: {{$reporte->calificacion}}/5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa {{ $i <= $reporte->calificacion ? 'fa-star' : 'fa-star-o' }}"></i>
                                    @endfor
                                </span>
                                <small class="text-muted">({{$reporte->calificacion}}/5)</small>
                            @else
                                <span class="text-muted">Sin calificar</span>
                            @endif
                        </div>

                        @if ($reporte->observaciones)
                            <div class="col-12 m-2">
                                <b>Observaciones del usuario: </b>
                                <div class="border rounded p-2 mt-1 bg-light">{{$reporte->observaciones}}</div>
                            </div>
                        @endif

                        @if ($reporte->detalles_solucion && $reporte->detalles_solucion != 'pendiente')
                            <div class="col-12 m-2">
                                <b>Detalles de la solución: </b>
                                <span>{{$reporte->detalles_solucion}}</span>
                            </div>
                        @endif

                        <div class="col-12">
                            @include('partials._adjuntos_reporte', ['adjuntos' => $reporte->adjuntos])
                        </div>


                    </div>
                </div>


                <div class="col-6 cascadia bg-light p-4 reportes-scroll border-0">
                    <h3>Seguimiento: </h3>

                    <div class="row">
                        @forelse ($comentarios as $comentario)
                            <div class="col-12 my-4">
                                <b class="font-size-18">{{($comentario->usuario == ($nombreAgente ?? null)) ? 'Yo' : $comentario->usuario }}: </b>
                                <p class="mb-0">{{$comentario->comentario}}</p>
                                <small class="fw-bold bd-highlight">{{Carbon::parse($comentario->created_at)->diffForHumans()}}</small>
                            </div>
                        @empty
                            <li>Sin comentarios aún</li>
                        @endforelse

                        @if ($reporte->status != 'completado')
                            <div class="col-12 mt-5 ">
                                <form action="{{route($rutaComentario ?? 'comentario.reporte.admin', $reporte->id)}}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <i class="fa fa-comment"></i>
                                        <b>{{$nombreAgente ?? ''}}: </b>
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

    
@endsection