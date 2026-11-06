{{--
    Lista de evidencia de un reporte. Espera $adjuntos (colección de
    AdjuntoReporte). Los archivos se sirven por adjuntos.show, que valida que
    quien los pide sea el dueño del reporte, un admin o un agente.
--}}
@php $total = $adjuntos->count(); @endphp

@if ($total > 0)
    <div class="evidencia-reporte">
        <b class="d-block mb-2">
            <i class="fa fa-paperclip me-1"></i>
            Evidencia ({{ $total }} archivo{{ $total === 1 ? '' : 's' }})
        </b>

        <div class="row g-2">
            @foreach ($adjuntos as $adjunto)
                <div class="col-12">
                    @if (! $adjunto->existeEnDisco())
                        <div class="text-danger small">
                            <i class="fa fa-triangle-exclamation me-1"></i>
                            {{ $adjunto->nombre_original }} — este archivo ya no está en el servidor.
                        </div>
                    @elseif ($adjunto->es_imagen)
                        <a href="{{ route('adjuntos.show', $adjunto->id) }}" target="_blank" rel="noopener">
                            <img src="{{ route('adjuntos.show', $adjunto->id) }}"
                                 class="img-fluid rounded border"
                                 style="max-height:420px"
                                 alt="{{ $adjunto->nombre_original }}">
                        </a>
                    @else
                        <a href="{{ route('adjuntos.show', $adjunto->id) }}"
                           class="d-flex align-items-center gap-2 text-decoration-none border rounded p-2 bg-light">
                            <i class="fa {{ $adjunto->icono() }} fa-lg text-secondary"></i>
                            <span>
                                <span class="d-block text-dark">{{ $adjunto->nombre_original }}</span>
                                <small class="text-muted">
                                    {{ $adjunto->mime ?: 'archivo' }} · {{ $adjunto->tamanoLegible() }}
                                </small>
                            </span>
                            <i class="fa fa-download ms-auto text-secondary"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
