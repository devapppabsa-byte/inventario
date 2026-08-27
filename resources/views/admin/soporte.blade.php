@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Solicitudes de Soporte Técnico')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
            <h2 class="fw-bold mb-4"><i class="fa fa-laptop-medical me-2"></i>Solicitudes de Soporte Técnico</h2>

            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Dispositivo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $reporte)
                        <tr>
                            <td>{{$reporte->user->name}}</td>
                            <td>{{$reporte->fecha_larga}}</td>
                            <td>{{$reporte->dispositivo}}</td>
                            <td>{{$reporte->descripcion}}</td>
                            <td>
                                @if ($reporte->status == 'completado')
                                    <span class="badge bg-success">Completado</span>
                                @else
                                    <span class="badge bg-danger">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa fa-check-circle fa-3x mb-3"></i><br>
                                No hay solicitudes de soporte
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
