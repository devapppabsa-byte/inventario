@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Pedidos de Tintas')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-10 col-lg-10 bg-white m-1 border border-5 p-4">
            <h1 class="py-3 fw-bold"><i class="fa fa-palette me-2"></i>Pedidos de Tintas</h1>

            @if (session('respuesta'))
                <div class="alert alert-success">{{session('respuesta')}}</div>
            @endif

            <table class="table table-bordered table-responsive-md p-0">
                @if (count($pedidos) > 0)
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Impresora</th>
                        <th>Colores</th>
                        <th>Respuesta Admin</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                @endif
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td>{{$pedido->user->name}}</td>
                            <td>{{$pedido->fecha_pedido}}</td>
                            <td>{{$pedido->numero}}</td>
                            <td>{{implode(', ', json_decode($pedido->colores, true))}}</td>
                            <td>{{$pedido->respuesta_admin}}</td>
                            <td>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#r{{$pedido->id}}">
                                    <i class="fa fa-comment"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#ped{{$pedido->id}}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-5">
                                <i class="fa-solid fa-droplet fa-5x text-muted"></i><br><br>
                                <h3 class="fw-bold text-muted">No hay pedidos</h3>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach ($pedidos as $pedido)
    <!-- Modal Respuesta -->
    <div class="modal fade" id="r{{$pedido->id}}" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <form action="{{route('respuesta.admin', $pedido->id)}}" method="POST">
                        @csrf @method('PATCH')
                        <div class="mb-3">
                            <h5 class="fw-bold">Escribe tu respuesta</h5>
                            <textarea class="form-control" name="respuesta_admin" placeholder="Respuesta al pedido..."></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Enviar</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Foto -->
    <div class="modal fade" id="ped{{$pedido->id}}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-3">
                    <img src="{{str_replace('storage/Public/', 'storage/', Storage::url($pedido->foto_tintas))}}" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
