@extends('layout')
@section('contenido')
@section('title', 'Inicio Admin')
@include('assets.nav')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">

            <h2 class="fw-bold mb-4"><i class="fa fa-home me-2"></i>Inicio</h2>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-palette fa-3x text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Pedidos de Tintas</h5>
                            <p class="card-text text-muted">Gestionar pedidos de tintas de impresora</p>
                            <a href="{{route('admin.pedidos.tintas')}}" class="btn btn-primary">Ver Pedidos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-laptop-medical fa-3x text-danger mb-3"></i>
                            <h5 class="card-title fw-bold">Soporte Técnico</h5>
                            <p class="card-text text-muted">Solicitudes de soporte técnico</p>
                            <a href="{{route('admin.soporte')}}" class="btn btn-danger">Ver Solicitudes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-users fa-3x text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Usuarios</h5>
                            <p class="card-text text-muted">Gestionar usuarios del sistema</p>
                            <a href="{{route('agregar.usuarios')}}" class="btn btn-success">Gestionar</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
