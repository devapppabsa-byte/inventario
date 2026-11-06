@extends('layout')
@include($navView ?? 'assets.nav')
@section('contenido')
@section('title', 'Solicitudes de Soporte Técnico')

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
            <h2 class="fw-bold mb-4"><i class="fa fa-laptop-medical me-2"></i>Solicitudes de Soporte Técnico</h2>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fa fa-filter me-1"></i> Filtros
                </div>
                <div class="card-body">
                    <form method="GET" action="{{route($rutaLista ?? 'admin.soporte')}}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Usuario</label>
                            <select name="usuario" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{$u->id}}" {{ request('usuario') == $u->id ? 'selected' : '' }}>{{$u->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dispositivo</label>
                            <select name="dispositivo" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($dispositivos as $d)
                                    <option value="{{$d}}" {{ request('dispositivo') == $d ? 'selected' : '' }}>{{$d}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Desde</label>
                            <input type="date" name="desde" class="form-control" value="{{request('desde')}}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Hasta</label>
                            <input type="date" name="hasta" class="form-control" value="{{request('hasta')}}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completados</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Estrellas</label>
                            <select name="estrellas" class="form-select">
                                <option value="">Todas</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{$i}}" {{ request('estrellas') == $i ? 'selected' : '' }}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button class="btn btn-dark fw-bold"><i class="fa fa-search me-1"></i>Filtrar</button>
                            <a href="{{route($rutaLista ?? 'admin.soporte')}}" class="btn btn-secondary fw-bold">Limpiar</a>
                            <a href="{{route($rutaExportar ?? 'admin.soporte.exportar', request()->query())}}" class="btn btn-outline-dark fw-bold">
                                <i class="fa fa-file-pdf me-1"></i>Exportar PDF
                            </a>
                            <span class="text-muted small align-self-center">
                                El PDF sale con los filtros que tengas aplicados ({{$totalReportes}} solicitud{{$totalReportes === 1 ? '' : 'es'}}).
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">{{session('error')}}</div>
            @endif

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Pendientes</h5>
                            <div class="display-4 fw-bold text-dark">{{ $pendientes }}</div>
                            <div class="text-muted small">
                                <a href="{{route($rutaLista ?? 'admin.soporte', array_merge(request()->query(), ['estado' => 'pendiente']))}}">Ver solo pendientes</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Completados</h5>
                            <div class="display-4 fw-bold text-dark">{{ $completados }}</div>
                            <div class="text-muted small">
                                <a href="{{route($rutaLista ?? 'admin.soporte', array_merge(request()->query(), ['estado' => 'completado']))}}">Ver solo completados</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Promedio</h5>
                            <div class="display-4 fw-bold text-dark">{{ $promedio !== null ? $promedio : '—' }}</div>
                            <div class="text-muted small">{{ $promedio !== null ? '/ 5' : 'Sin datos' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">% Cumplimiento</h5>
                            <div class="display-4 fw-bold text-dark">
                                {{ $promedio !== null ? round(($promedio / 5) * 100) : '—' }}@if($promedio !== null)<span class="fs-3">%</span>@endif
                            </div>
                            <div class="text-muted small">
                                {{ $promedio !== null ? 'sobre el máximo (5)' : 'Sin datos' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Total evaluados</h5>
                            <div class="display-4 fw-bold text-dark">{{ $totalEvaluados }}</div>
                            <div class="text-muted small">reportes con calificación</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Distribución</h5>
                            @php
                                $maxN = max($distribucion ?: [0]);
                            @endphp
                            <div class="text-start">
                                @for ($i = 1; $i <= 5; $i++)
                                    @php $n = $distribucion[$i] ?? 0; @endphp
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="me-2 fw-bold" style="width:42px">{{$i}} <i class="fa fa-star text-warning"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height:14px;">
                                                <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $maxN > 0 ? ($n / $maxN) * 100 : 0 }}%"></div>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-muted small" style="width:40px; text-align:right;">{{$n}}</div>
                                    </div>
                                @endfor
                            </div>
                            @if ($totalEvaluados == 0)
                                <div class="text-muted small mt-2">Sin calificaciones para los filtros seleccionados.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
            <div class="tabla-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Dispositivo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $reporte)
                        <tr>
                            <td data-label="Usuario">{{$reporte->user->name}}</td>
                            <td data-label="Fecha">{{$reporte->fecha_larga}}</td>
                            <td data-label="Dispositivo">{{$reporte->dispositivo}}</td>
                            <td data-label="Descripción">
                                @include('partials._descripcion_enriquecida', ['descripcion' => $reporte->descripcion])
                                @if ($reporte->adjuntos->isNotEmpty())
                                    <span class="badge bg-secondary mt-1" title="{{ $reporte->adjuntos->count() }} archivo(s) de evidencia">
                                        <i class="fa fa-paperclip me-1"></i>{{ $reporte->adjuntos->count() }}
                                    </span>
                                @endif
                            </td>
                            <td data-label="Estado">
                                @if ($reporte->status == 'completado')
                                    <span class="badge bg-success">Completado</span>
                                @else
                                    <span class="badge bg-danger">Pendiente</span>
                                @endif
                            </td>
                            <td data-label="Calificación" class="text-center">
                                @if ($reporte->calificacion)
                                    <span class="text-warning" title="Calificación: {{$reporte->calificacion}}/5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa {{ $i <= $reporte->calificacion ? 'fa-star' : 'fa-star-o' }}"></i>
                                        @endfor
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-label="Acción" class="text-center">
                                <a href="{{route($rutaDetalle ?? 'detalle.reporte.admin', $reporte->id)}}" class="btn btn-dark btn-sm">
                                    <i class="fa fa-eye me-1"></i> Seguimiento
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="sin-etiqueta text-center py-5 text-muted" colspan="7">
                                <i class="fa fa-check-circle fa-3x mb-3"></i><br>
                                No hay solicitudes de soporte
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Mostrando {{$reportes->count()}} de {{$reportes->total()}} solicitud{{$reportes->total() === 1 ? '' : 'es'}}
                </small>
                <div>{{$reportes->links()}}</div>
            </div>
        </div>
    </div>
</div>

@endsection
