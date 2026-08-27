@extends('layout')
@include('assets.nav')
@section('contenido')
@section('title', 'Configuración de Correo')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">

        <div class="col-sm-12 col-md-10 col-lg-8">
            <h2 class="fw-bold mb-4"><i class="fa fa-cog me-2"></i>Configuración de Correo Electrónico</h2>

            @if (session('config_guardada'))
                <div class="alert alert-success">{{session('config_guardada')}}</div>
            @endif

            {{-- PEDIDOS DE TINTAS --}}
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="fw-bold mb-0"><i class="fa fa-palette me-2"></i>Pedidos de Tintas</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.config.tintas.update')}}" method="POST">
                        @csrf @method('PATCH')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo remitente</label>
                                <input type="email" name="mail_from" class="form-control" value="{{old('mail_from', $tintas->mail_from ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre del remitente</label>
                                <input type="text" name="mail_from_name" class="form-control" value="{{old('mail_from_name', $tintas->mail_from_name ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo destinatario (para)</label>
                                <input type="email" name="destinatario" class="form-control" value="{{old('destinatario', $tintas->destinatario ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correos en copia (CC) — separados por coma</label>
                                <input type="text" name="cc" class="form-control" value="{{old('cc', $tintas->cc ?? '')}}" placeholder="correo1@ejemplo.com, correo2@ejemplo.com">
                            </div>
                        </div>
                        <button class="btn btn-success"><i class="fa fa-save me-1"></i>Guardar</button>
                    </form>
                </div>
            </div>

            {{-- SOLICITUDES DE SOPORTE TECNICO --}}
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="fw-bold mb-0"><i class="fa fa-laptop-medical me-2"></i>Solicitudes de Soporte Técnico</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.config.soporte.update')}}" method="POST">
                        @csrf @method('PATCH')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo remitente</label>
                                <input type="email" name="mail_from" class="form-control" value="{{old('mail_from', $soporte->mail_from ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre del remitente</label>
                                <input type="text" name="mail_from_name" class="form-control" value="{{old('mail_from_name', $soporte->mail_from_name ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo destinatario (para)</label>
                                <input type="email" name="destinatario" class="form-control" value="{{old('destinatario', $soporte->destinatario ?? '')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correos en copia (CC) — separados por coma</label>
                                <input type="text" name="cc" class="form-control" value="{{old('cc', $soporte->cc ?? '')}}" placeholder="correo1@ejemplo.com, correo2@ejemplo.com">
                            </div>
                        </div>
                        <button class="btn btn-success"><i class="fa fa-save me-1"></i>Guardar</button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection
