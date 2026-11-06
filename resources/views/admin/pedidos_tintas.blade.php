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

            @if (session('completado'))
                <div class="alert alert-success">{{session('completado')}}</div>
            @endif

            @if ($errors->first('entrega') || $errors->first('pendientes') || $errors->first('cantidad_pendiente'))
                <div class="alert alert-danger">{{ $errors->first('entrega') ?: $errors->first('pendientes') ?: $errors->first('cantidad_pendiente') }} Vuelve a abrir Completar e inténtalo de nuevo.</div>
            @endif

            @if (session('correo_fallido'))
                <div class="alert alert-warning">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    El pedido quedó guardado, pero el aviso por correo del residual no se pudo entregar.
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fa fa-filter me-1"></i> Filtros
                </div>
                <div class="card-body">
                    <form method="GET" action="{{route('admin.pedidos.tintas')}}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Desde</label>
                            <input type="date" name="desde" class="form-control" value="{{request('desde')}}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Hasta</label>
                            <input type="date" name="hasta" class="form-control" value="{{request('hasta')}}">
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button class="btn btn-dark fw-bold"><i class="fa fa-search me-1"></i>Filtrar</button>
                            <a href="{{route('admin.pedidos.tintas')}}" class="btn btn-secondary fw-bold">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Total pedidos</h5>
                            <div class="display-4 fw-bold text-dark">{{ $totalPedidos }}</div>
                            <div class="text-muted small">en el rango seleccionado</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Completados</h5>
                            <div class="display-4 fw-bold text-dark">{{ $completados }}</div>
                            <div class="text-muted small">pedidos entregados</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Parciales</h5>
                            <div class="display-4 fw-bold text-dark">{{ $parciales }}</div>
                            <div class="text-muted small">entregas incompletas</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">Pendientes</h5>
                            <div class="display-4 fw-bold text-dark">{{ $pendientes }}</div>
                            <div class="text-muted small">pedidos por entregar</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <h5 class="text-muted fw-bold mb-2">% Cumplimiento</h5>
                            <div class="display-4 fw-bold text-dark">
                                {{ $porcentajeCumplimiento !== null ? $porcentajeCumplimiento : '—' }}@if($porcentajeCumplimiento !== null)<span class="fs-3">%</span>@endif
                            </div>
                            <div class="text-muted small">
                                {{ $porcentajeCumplimiento !== null ? 'completados vs total' : 'Sin datos' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fa fa-trophy me-1"></i> Top 5 usuarios con más pedidos
                </div>
                <div class="card-body">
                    @if ($topUsuarios->count() > 0)
                        @php
                            $maxTop = $topUsuarios->max('n') ?: 1;
                        @endphp
                        @foreach ($topUsuarios as $tu)
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2 fw-bold" style="width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{$tu->user->name ?? 'Sin usuario'}}</div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height:16px;">
                                        <div class="progress-bar bg-dark" role="progressbar" style="width: {{ ($tu->n / $maxTop) * 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="ms-2 text-muted small" style="width:50px; text-align:right;">{{$tu->n}}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">Sin pedidos en el rango seleccionado.</div>
                    @endif
                </div>
            </div>

            <div class="tabla-responsive">
            <table class="table table-bordered p-0">
                @if (count($pedidos) > 0)
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Impresora</th>
                        <th>Colores</th>
                        <th>Estado</th>
                        <th>Completado por</th>
                        <th>Residuales</th>
                        <th>Respuesta Admin</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                @endif
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td data-label="Usuario">{{$pedido->user->name}}</td>
                            <td data-label="Fecha">{{$pedido->fecha_pedido}}</td>
                            <td data-label="Impresora">{{$pedido->numero}}</td>
                            <td data-label="Colores">{{implode(', ', json_decode($pedido->colores, true) ?: [])}}</td>
                            <td data-label="Estado" class="text-start">
                                @if ($pedido->status === null || strcasecmp($pedido->status, 'pendiente') === 0)
                                    <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#c{{$pedido->id}}">
                                        <i class="fa fa-check"></i> Completar
                                    </button>
                                @elseif (strcasecmp($pedido->status, 'Entrega parcial') === 0)
                                    <i class="fa fa-triangle-exclamation"></i> Entrega parcial
                                    @if ($pedido->hijos->isNotEmpty())
                                        <small class="d-block text-muted">Faltan: {{ implode(', ', json_decode($pedido->hijos->first()->colores, true) ?: []) }}</small>
                                    @endif
                                @else
                                    <i class="fa fa-check-circle"></i> Completado
                                    @if ($pedido->hijos->isNotEmpty())
                                        <small class="d-block text-muted">Quedaron: {{ implode(', ', json_decode($pedido->hijos->last()->colores, true) ?: []) }}</small>
                                    @endif
                                @endif
                            </td>
                            <td data-label="Completado por">{{ $pedido->completado_por ?: '—' }}</td>
                            <td data-label="Residuales">
                                @if ($pedido->parent_id)
                                    <span class="badge bg-secondary">Residual de #{{ $pedido->parent_id }}</span>
                                @endif
                                @if ($pedido->residuales > 0)
                                    <span class="badge bg-dark">{{ $pedido->residuales }} residual{{ $pedido->residuales > 1 ? 'es' : '' }}</span>
                                @endif
                                @if (! $pedido->parent_id && $pedido->residuales == 0)
                                    —
                                @endif
                            </td>
                            <td data-label="Respuesta Admin">{{$pedido->respuesta_admin}}</td>
                            <td data-label="Detalles">
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
                            <td class="sin-etiqueta text-center p-5" colspan="9">
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
</div>

@foreach ($pedidos as $pedido)
    <!-- Modal Respuesta -->
    <div class="modal fade" id="r{{$pedido->id}}" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form action="{{route('respuesta.admin', $pedido->id)}}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-bold">Escribe tu respuesta</h5>
                            <textarea class="form-control" name="respuesta_admin" placeholder="Respuesta al pedido..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Enviar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Foto -->
    <div class="modal fade" id="ped{{$pedido->id}}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-3">
                    <img src="{{Storage::url($pedido->foto_tintas)}}" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Completar -->
    <div class="modal fade" id="c{{$pedido->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form id="form-c{{$pedido->id}}" action="{{route('admin.pedidos.completar', $pedido->id)}}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body p-4 text-center">
                        <h5 class="fw-bold">¿Se entregaron todas las tintas?</h5>
                        <p class="text-muted small mb-0">Si faltaron, se genera un pedido residual solo con lo que falta.</p>
                        <div id="pend-{{$pedido->id}}" class="text-start mt-3 d-none">
                            @if ($pedido->numero === 'Cintas')
                                @php $totalCintas = (int) (json_decode($pedido->colores, true)[0] ?? 0); @endphp
                                <label class="form-label fw-bold">Cintas pendientes (de {{ $totalCintas }})</label>
                                <input type="number" name="cantidad_pendiente" class="form-control" min="1" max="{{ max(1, $totalCintas) }}">
                            @else
                                <label class="form-label fw-bold">Marca las tintas que FALTAN</label>
                                @foreach (json_decode($pedido->colores, true) ?: [] as $color)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pendientes[]" value="{{ $color }}" id="p{{$pedido->id}}-{{ $loop->index }}">
                                        <label class="form-check-label" for="p{{$pedido->id}}-{{ $loop->index }}">{{ $color }}</label>
                                    </div>
                                @endforeach
                            @endif
                            <div id="aviso-{{$pedido->id}}" class="text-danger small mt-2 d-none"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" name="entrega" value="total" class="btn btn-success">
                            <i class="fa fa-check"></i> Sí, todas
                        </button>
                        <button type="button" id="btn-parcial-{{$pedido->id}}" class="btn btn-warning" onclick="marcarParcial({{$pedido->id}})">
                            <i class="fa fa-triangle-exclamation"></i> No, faltaron
                        </button>
                        <button type="submit" name="entrega" value="parcial" id="btn-generar-{{$pedido->id}}" class="btn btn-warning d-none" onclick="return validarParcial({{$pedido->id}})">
                            Marcar parcial y crear residual
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
function marcarParcial(id){
    const pend = document.getElementById('pend-'+id);
    if (!pend || !pend.classList.contains('d-none')) return;
    pend.classList.remove('d-none');
    const parcial = document.getElementById('btn-parcial-'+id);
    if (parcial) parcial.classList.add('d-none');
    const generar = document.getElementById('btn-generar-'+id);
    if (generar) generar.classList.remove('d-none');
}

function validarParcial(id){
    const modal = document.getElementById('c'+id);
    const aviso = document.getElementById('aviso-'+id);
    let error = '';
    const cintas = modal ? modal.querySelector('input[name="cantidad_pendiente"]') : null;
    if (cintas) {
        const total = parseInt(cintas.max, 10) || 0;
        const v = parseInt(cintas.value, 10);
        if (cintas.value === '') error = 'Indica cuantas cintas quedaron pendientes.';
        else if (isNaN(v) || v < 1) error = 'Deben quedar al menos 1 cinta pendiente.';
        else if (v > total) error = 'No pueden quedar mas cintas de las pedidas (' + total + ').';
        else if (v >= total) error = 'Si faltan todas las cintas el pedido no se entrego: dejalo como pendiente.';
    } else if (modal) {
        const todas = modal.querySelectorAll('input[name="pendientes[]"]');
        const marcadas = modal.querySelectorAll('input[name="pendientes[]"]:checked');
        if (marcadas.length === 0) error = 'Marca las tintas que quedaron pendientes.';
        else if (marcadas.length >= todas.length) error = 'Si faltan todas las tintas el pedido no se entrego: dejalo como pendiente.';
    }
    if (error) {
        if (aviso) {
            aviso.textContent = error;
            aviso.classList.remove('d-none');
        }
        return false;
    }
    if (aviso) aviso.classList.add('d-none');
    return true;
}
</script>

@endsection
