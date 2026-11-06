@extends('layout')
@section('title', 'Contestando evaluación')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid mt-4" id="content">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            @if (session('ok'))
                <h5 class="text-success fw-bold">{{session('ok')}}</h5>
            @endif

            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0">{{$intento->evaluacion->titulo}}</h3>
                        <span class="badge bg-success"><i class="fa fa-truck me-1"></i>{{$intento->proveedor->nombre}}</span>
                    </div>
                    <small class="text-muted">Iniciado: {{$intento->fecha_inicio->format('d/m/Y H:i')}}</small>
                </div>
            </div>

            <form id="formIntento" action="{{route('evaluaciones.guardar', $intento->id)}}" method="POST">
                @csrf
                <input type="hidden" name="action" id="actionInput" value="">

                @forelse ($secciones as $seccion)
                    <div class="card border shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="fw-bold mb-0"><i class="fa fa-layer-group me-1"></i>{{$seccion->titulo}}</h5>
                            @if ($seccion->descripcion)
                                <small class="text-muted">{{$seccion->descripcion}}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            @forelse ($seccion->preguntas as $pregunta)
                                <div class="mb-4">
                                    <label class="fw-bold d-block">{{$pregunta->pregunta}}
                                        <span class="text-muted fw-normal">({{$pregunta->puntos_maximos}} pts)</span>
                                    </label>
                                    @foreach ($pregunta->opciones as $opcion)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pregunta_{{$pregunta->id}}"
                                                value="{{$opcion->id}}"
                                                id="q{{$pregunta->id}}o{{$opcion->id}}"
                                                {{ isset($respuestas[$pregunta->id]) && $respuestas[$pregunta->id]->opcion_id == $opcion->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="q{{$pregunta->id}}o{{$opcion->id}}">
                                                {{$opcion->texto}}
                                                <small class="text-muted">({{$opcion->puntos}} pts)</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <small class="text-muted">Sin preguntas en esta sección.</small>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning">Esta evaluación no tiene secciones con preguntas.</div>
                @endforelse

                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <label class="fw-bold">Comentario general (opcional)</label>
                        <textarea name="comentario" rows="3" class="form-control" placeholder="Observaciones sobre el proveedor...">{{$intento->comentario}}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <button type="submit" name="action" value="guardar" class="btn btn-warning">
                        <i class="fa fa-save"></i> Guardar progreso
                    </button>
                    <button type="submit" name="action" value="finalizar" class="btn btn-success">
                        <i class="fa fa-check"></i> Finalizar evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmarFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fa fa-exclamation-triangle me-1"></i>Confirmar finalización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="mb-2 fs-5">Tienes <strong id="cantidadFaltantes">0</strong> pregunta(s) sin contestar.</p>
                <p class="text-muted mb-0">Se evaluarán con <strong class="text-dark">0 puntos</strong>. ¿Estás seguro de finalizar así?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> No, seguir contestando
                </button>
                <button type="button" id="btnConfirmarFinalizar" class="btn btn-success">
                    <i class="fa fa-check me-1"></i> Sí, finalizar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('formIntento');
    const actionInput = document.getElementById('actionInput');
    const modalEl = document.getElementById('confirmarFinalizar');
    const cantidadFaltantes = document.getElementById('cantidadFaltantes');
    const btnConfirmar = document.getElementById('btnConfirmarFinalizar');

    if (form && modalEl && btnConfirmar) {
        form.addEventListener('submit', function(e){
            const submitter = e.submitter;
            if (!submitter || submitter.value !== 'finalizar') return;

            const nombres = new Set();
            form.querySelectorAll('input[type="radio"][name^="pregunta_"]').forEach(r => nombres.add(r.name));
            const total = nombres.size;
            let contestadas = 0;
            nombres.forEach(n => {
                if (form.querySelector('input[name="'+n+'"]:checked')) contestadas++;
            });
            const faltantes = total - contestadas;

            if (faltantes > 0) {
                e.preventDefault();
                cantidadFaltantes.textContent = faltantes;
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        });

        btnConfirmar.addEventListener('click', function(){
            actionInput.value = 'finalizar';
            form.submit();
        });
    }
});
</script>

@endsection
