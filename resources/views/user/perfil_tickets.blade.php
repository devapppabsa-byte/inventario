@extends('layout')
@section('contenido')
@include('user.cabecera') 
@section('title', 'Reportes de fallas')


{{-- 
<div class="container">
  <iframe src="https://www.milenio.com/" width="100%" height="700px"></iframe>
</div> --}}
<style>
  select.color-select option.green {
    background-color: #d4d4d4;
    color: #212529;
  }

  select.color-select option.red {
    background-color: #adb5bd;
    color: #212529;
  }

  select.color-select option.yellow {
    background-color: #e9ecef;
    color: #343a40;
  }
</style>

<div class="container" id="content">

    <div class="row">
        <div class="col-12 text-center">
            @if (session('reportado'))
                <h5 class="fw-bold text-center text-success">
                  {{session('reportado')}}
                </h5>
            @endif
            @if (session('reenviado'))
                <h5 class="fw-bold text-center text-success">
                  {{session('reenviado')}}
                </h5>
            @endif
            @if (session('completado'))
                <h5 class="fw-bold text-center text-success">
                  {{session('completado')}}
                </h5>
            @endif
            @if (session('correo_fallido'))
                <div class="alert alert-warning">
                  <p class="mb-0">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    Tu reporte quedó guardado, pero el aviso por correo no se pudo entregar.
                    Usa «Reenviar aviso» en la tabla para intentarlo de nuevo.
                  </p>
                </div>
            @endif
        </div>
    </div>


    <div class="row d-flex justify-content-around">
        
        <div class="col-12 mx-0 mx-md-1 bg-white p-4 shadow shadow-sm">
            <button class="btn btn-dark btn-sm m-1 fw-bold" data-bs-toggle="modal" data-bs-target="#reporte">
                <i class="fa fa-plus"></i>
                Realizar reporte
            </button>

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <h3 class="text-center">
              Reportes realizados
            </h3>
            <div class="tabla-responsive">
            <table class="table table-bordered" style="transition: 3s">
              @if (count($reportes) > 0)
                <thead class="table-dark ">
                  <tr>
                      <th scope="col">ID Ticket</th>
                      <th scope="col">Lo que falla</th>
                      <th scope="col">Fecha de reporte</th>
                      <th scope="col">Detalles</th>
                      <th scope="col">Estado</th>
                      <th scope="col">Calificación</th>
                      <th scope="col">Ver Seguimiento</th>
                      <th scope="col">Reenviar aviso</th>

                  </tr>
                </thead>
              @endif
                <tbody>
                   
                @forelse ($reportes as $reporte)
                  @if ($reporte->status == 'pendiente')
                      <tr>
                        <td data-label="ID Ticket" class="text-center">{{$reporte->id}}</td>
                        <td data-label="Lo que falla">
                          {{$reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo}}
                        </td>
                        <td data-label="Fecha de reporte"><small>{{$reporte->fecha_reporte}}</small></td>
                        {{-- HTML ya saneado con HTMLPurifier al guardarse --}}
                        <td data-label="Detalles"><div class="descripcion-enriquecida celda-corta">{!! $reporte->descripcion !!}</div></td>

                        <td data-label="Estado" class="text-start">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Pendiente
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#c{{$reporte->id}}" style="cursor: pointer">Completo</a>
                                </div>
                            </div>                            
                        </td>   
                        <td data-label="Calificación" class="text-center text-muted">—</td>
                        <td data-label="Ver Seguimiento" class="text-center">
                          <a href="{{route('detalle.reporte', $reporte->id)}}" class="btn btn-dark btn-sm">
                            <i class="fa fa-eye"></i> 
                          </a>
                        </td>   
                        <td data-label="Reenviar aviso" class="{{in_array($reporte->id, $reenviables ?? []) ? '' : 'celda-vacia'}}">
                          @if (in_array($reporte->id, $reenviables ?? []))
                            <button class="btn btn-outline-danger btn-sm fw-bold boton-reenviar" data-bs-toggle="modal" data-bs-target="#r{{$reporte->id}}">
                              <i class="fa fa-paper-plane"></i> Reenviar aviso
                            </button>
                          @endif
                        </td>
                      </tr>
                  @else
                      <tr>
                        <td data-label="ID Ticket" class="text-center">{{$reporte->id}}</td>
                        <td data-label="Lo que falla">
                          <a href="{{route('detalle.reporte', $reporte->id)}}">
                            {{$reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo}}
                          </a>
                        </td>
                        <td data-label="Fecha de reporte"><small>{{$reporte->fecha_reporte}}</small></td>
                        {{-- HTML ya saneado con HTMLPurifier al guardarse --}}
                        <td data-label="Detalles"><div class="descripcion-enriquecida celda-corta">{!! $reporte->descripcion !!}</div></td>
                        <td data-label="Estado" class="text-start">
                          <i class="fa fa-check-circle"></i> Resuelto
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
                        <td data-label="Ver Seguimiento" class="text-center">
                          <a href="{{route('detalle.reporte', $reporte->id)}}" class="btn btn-dark btn-sm">
                            <i class="fa fa-eye"></i>
                          </a>
                        </td>
                        <td data-label="Reenviar aviso" class="{{in_array($reporte->id, $reenviables ?? []) ? '' : 'celda-vacia'}}">
                          @if (in_array($reporte->id, $reenviables ?? []))
                            <button class="btn btn-outline-danger btn-sm fw-bold boton-reenviar" data-bs-toggle="modal" data-bs-target="#r{{$reporte->id}}">
                              <i class="fa fa-paper-plane"></i> Reenviar aviso
                            </button>
                          @endif
                        </td>
                      </tr>     
                      
                  @endif
                
                @empty
         
                <div class="row justify-content-center p-5">
                  <div class="col-8 text-center mt-5">
                    <i class="fa-solid fa-pencil fa-4x m-3"></i>
                    <h4>No hay reportes aqui</h4>
                  </div>
                </div>
                @endforelse



             
                    
                    
                </tbody>
            </table>
            </div>
            <small>Página: {{$reportes->currentPage()}}</small> 
            <div class="row m-2">
              <div class="col-12 text-center bg-white">
                {{$reportes->links()}}
              </div>
            </div>

        </div>
    </div>

 </div> {{-- ciertre del container que cierra todo --}}




@forelse ($reportes as $reporte)
    

<!-- Modal -->
<div class="modal fade" id="c{{$reporte->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-dark text-white border-0">
          <h5 class="modal-title fw-bold"><i class="fa fa-check-circle me-1"></i>Completar soporte</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('reporte.completo', $reporte->id)}}" method="POST">
          @csrf @method('PATCH')
          <div class="modal-body">
            <p class="text-center fs-2 fw-bold text-dark mb-4">¿Cómo calificas el soporte recibido?</p>

            <div class="bg-light rounded-3 border py-5 px-3 text-center mb-4 rating-panel" id="panel-{{$reporte->id}}">
              <input type="hidden" name="calificacion" id="calificacion-{{$reporte->id}}" value="0">
              <div class="rating-estrellas lh-1 mb-3" id="rating-{{$reporte->id}}" style="font-size: 8rem;">
                @for ($i = 1; $i <= 5; $i++)
                  <span class="estrella me-2 d-inline-block" data-valor="{{$i}}" style="cursor:pointer; color:#adb5bd; transition: color .15s;">&#9733;</span>
                @endfor
              </div>
              <div class="calificacion-num fs-1 fw-bold text-dark" id="calificacion-num-{{$reporte->id}}">0 / 5</div>
              <div class="calificacion-desc fw-bold text-muted fs-4 mt-1" id="calificacion-desc-{{$reporte->id}}">Elige tu calificación</div>
            </div>

            <div class="mb-2">
              <label class="form-label fw-bold">Observaciones <span class="text-muted">(opcional)</span></label>
              <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalla cómo se resolvió el problema...">{{old('observaciones')}}</textarea>
            </div>
            @if ($errors->has('calificacion'))
              <div class="text-danger small fw-bold">{{$errors->first('calificacion')}}</div>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-success">
              <i class="fa fa-check"></i>
              Confirmar
            </button>
          </div>
        </form>
      </div>
    </div>
</div>
  <!-- Modal -->
  

  
  <!-- Modal -->
  @if (in_array($reporte->id, $reenviables ?? []))
  <div class="modal fade" id="r{{$reporte->id}}" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
            <div class="modal-body">
              <h4>¿Reenviar el aviso del reporte?</h4>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <form action="{{route('reporte.reenvio', $reporte->id)}}" method="POST">
                @csrf
                <button class="btn btn-success">
                  <i class="fa fa-paper-plane"></i>
                  Reenviar
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal -->
  @endif




      
@empty
@endforelse
      



          <!-- Modal -->
          <div class="modal fade" id="reporte" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-0">
                  <h5 class="modal-title fw-bold"><i class="fa fa-laptop-medical me-2"></i>Nuevo reporte</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                  <form action="{{route('reporte.post')}}" id="reporte_send" enctype="multipart/form-data" method="post">
                    @csrf @method('post')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción de la falla</label>
                        {{-- Quill se monta sobre este div; el input oculto es el que se
                             envía. Quill 2.0.3 ya viene incluido en resources/views/layout.blade.php.
                             La clase editor-alto le fija el alto con scroll interno (estilos.css). --}}
                        <div id="descripcion" class="editor-alto"></div>
                        <input type="hidden" name="descripcion" id="descripcion_input" value="">
                        {{-- Lo que ya habia escrito el usuario, separado del input para no
                             meter HTML dentro de un atributo. JSON_HEX_TAG evita que un
                             </script> dentro del texto cierre este bloque. --}}
                        <script type="application/json" id="descripcion_previa">{!! json_encode(old('descripcion', ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                        @error('descripcion')
                            <small class="text-danger fw-bold d-block">{{$message}}</small>
                        @enderror
                    </div>
                      <div class="mb-3">
                        <label class="form-label fw-bold">Selecciona el dispositivo</label>

                        <select name="dispositivo" id="dispositivo" class="form-control">
                          <option value="computadora" {{ old('dispositivo') == 'computadora' ? 'selected' : '' }}>Computadora</option>
                          <option value="impresora" {{ old('dispositivo') == 'impresora' ? 'selected' : '' }}>Impresora</option>
                          <option value="telefono" {{ old('dispositivo') == 'telefono' ? 'selected' : '' }}>Teléfono</option>
                          <option value="Otro" {{ old('dispositivo') == 'Otro' ? 'selected' : '' }}>Otro dispositivo</option>
                        </select>
                        @error('dispositivo')
                            <small class="text-danger fw-bold d-block">{{$message}}</small>
                        @enderror
                      </div>

                      {{-- Se muestra desde el inicio si la validación anterior dejó "Otro" elegido --}}
                      <div class="mb-3 {{ old('dispositivo') == 'Otro' ? '' : 'd-none' }}" id="otra_falla">
                        <label class="form-label fw-bold">¿Que es lo que falla?</label>
                        <input type="text" class="form-control" id="otro" name="otro" value="{{old('otro')}}">
                        @error('otro')
                            <small class="text-danger fw-bold d-block">{{$message}}</small>
                        @enderror
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Prioridad</label>
                        <select name="prioridad" id="prioridad" class="form-control">
                          <option class="green"  value="Baja" {{ old('prioridad') == 'Baja' ? 'selected' : '' }}>
                            🟢 Baja
                          </option>
                          <option class="yellow" value="Media" {{ old('prioridad') == 'Media' ? 'selected' : '' }}>
                            🟡 Media
                          </option>
                          <option class="red"    value="Alta"  {{ old('prioridad') == 'Alta'  ? 'selected' : '' }}>
                            🔴 Alta
                          </option>
                        </select>
                        @error('prioridad')
                            <small class="text-danger fw-bold d-block">{{$message}}</small>
                        @enderror
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Evidencia <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="file" name="adjuntos[]" class="form-control" id="falla_picture" multiple
                               accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,
                                       application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                                       application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                                       text/csv,application/vnd.ms-powerpoint,
                                       application/vnd.openxmlformats-officedocument.presentationml.presentation">
                        <div class="border rounded p-3">
                          <dl class="row mb-0">
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Formatos</dt>
                            <dd class="col-8 col-sm-9">Imágenes JPG, PNG, GIF o WEBP · Documentos PDF, Word, Excel, PowerPoint o CSV</dd>
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Archivos</dt>
                            <dd class="col-8 col-sm-9">Hasta {{ $limites['maxArchivos'] }} adjuntos</dd>
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Peso total</dt>
                            <dd class="col-8 col-sm-9">{{ round($limites['maxTotalKb'] / 1024, 1) }} MB entre todos los archivos</dd>
                            <dt class="col-4 col-sm-3 text-muted fw-bold">Imágenes</dt>
                            <dd class="col-8 col-sm-9">Mínimo 300×300 px</dd>
                          </dl>
                        </div>
                        <div class="form-text mt-2">
                          Las imágenes demasiado grandes se reducen automáticamente a 1600 px
                          antes de enviarse, para que no tengas que ajustarlas tú.
                        </div>
                        @php
                            // Las reglas por archivo (formato, peso) fallan con claves
                            // tipo "adjuntos.0", asi que el bloque de error tiene que
                            // recoger todas las que empiezan por "adjuntos" y no solo
                            // la exacta.
                            $erroresAdjuntos = collect($errors->getBag('default')->getMessages())
                                ->filter(function ($mensajes, $clave) {
                                    return str_starts_with($clave, 'adjuntos');
                                })
                                ->flatten();
                        @endphp
                        @if ($erroresAdjuntos->isNotEmpty())
                            <div class="text-danger fw-bold small">
                              @foreach ($erroresAdjuntos as $error)
                                <div>{{$error}}</div>
                              @endforeach
                            </div>
                        @endif
                        <div class="col-12 text-center p-4" id="container_falla_picture"></div>
                      </div>
                  </form>
                </div>

                <div class="modal-footer">
                  {{-- El boton vive fuera del <form> (el modal-footer es hermano del
                       modal-body), asi que se enlaza con el atributo form= de HTML5.
                       data-loader-bloquea le avisa al watchdog de loader-modal.js que
                       este control se deshabilita al enviar y se vuelve a habilitar
                       si la peticion se cuelga. --}}
                  <button type="submit" form="reporte_send" class="btn btn-dark" id="enviar_reporte" data-loader-bloquea>
                    <i class="fa fa-check me-1"></i>
                    Confirmar
                  </button>
                  <button type="button" id="cerrar_formulario_ticket" class="btn btn-secondary" data-bs-dismiss="modal" data-loader-bloquea>Cerrar</button>
                </div>
              </div>
            </div>
          </div>
          <!-- Modal -->



      {{-- aqui esta el html de mi menu contextual --}}
<div class="menu-contextual" id="menuContextual">
  <ul>
      <li>
        <a data-bs-toggle="modal" data-bs-target="#reporte">
          <i class="fa fa-plus"></i>
            Realizar Reporte
        </a>
      </li>
  </ul>
</div>


{{-- aqui van los scripts de esta seccion --}}

<script>

document.getElementById('dispositivo').addEventListener('change', function(){

  const otro = document.getElementById('otra_falla');

  if(this.value === 'Otro'){
    otro.classList.remove('d-none');
    otro.classList.add('d-block');
  }
  else{
    otro.classList.add('d-none');
    otro.classList.remove('d-block');
  }
})

</script>

{{-- Editor de texto enriquecido. Quill 2.0.3 ya viene en el layout general;
     solo se monta sobre el <div id="descripcion"> y se copia su HTML al input
     oculto que es el que viaja en el POST. --}}
<script>
  const editorDescripcion = new Quill('#descripcion', {
    theme: 'snow',
    placeholder: 'Describe la falla...',
    modules: {
      toolbar: [
        [{ header: [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link']
      ]
    }
  });

  // old('descripcion') para que no se pierda lo ya escrito si falla la validacion.
  // Vive en un <script type="application/json">, no en el value del input, porque
  // el texto es HTML y dentro de un atributo se romperia con comillas o saltos.
  const descripcionPrevia = JSON.parse(document.getElementById('descripcion_previa').textContent);
  if (descripcionPrevia) {
    editorDescripcion.root.innerHTML = descripcionPrevia;
  }
  document.getElementById('descripcion_input').value = editorDescripcion.root.innerHTML;

  editorDescripcion.on('text-change', function(){
    document.getElementById('descripcion_input').value = editorDescripcion.root.innerHTML;
  });
</script>

<script>
  // Vista previa de los archivos elegidos: las imágenes se muestran, los
  // documentos solo se listan porque el navegador no las previsualiza.
  function aviso(texto) {
    const div = document.createElement('div');
    div.className = 'alert alert-warning py-2 small text-start';
    div.textContent = texto;
    return div;
  }

  const TOPES_EVIDENCIA = @json([
      'maxArchivos' => $limites['maxArchivos'],
      'maxTotalKb'  => $limites['maxTotalKb'],
  ]);

  const inputEvidencia = document.getElementById('falla_picture');
  const contenedorEvidencia = document.getElementById('container_falla_picture');

  // Promesa de la compresion en curso. El envio espera a que termine para no
  // mandar los archivos originales, que es justo lo que desborda el limite de
  // 4.5 MB de Vercel.
  let compresionEnCurso = null;
  let enviarDeNuevo = false;

  function pesoLegible(bytes) {
    if (bytes >= 1048576) {
      return (bytes / 1048576).toFixed(1) + ' MB';
    }
    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  function pintarEvidencia(detalle) {
    contenedorEvidencia.innerHTML = '';

    if (!detalle.length) {
      return;
    }

    if (detalle.length > TOPES_EVIDENCIA.maxArchivos) {
      contenedorEvidencia.appendChild(aviso('Seleccionaste ' + detalle.length
        + ' archivos; el máximo es ' + TOPES_EVIDENCIA.maxArchivos + '.'));
    }

    const total = detalle.reduce(function (suma, item) {
      return suma + item.tamano;
    }, 0);
    const maximoTotal = TOPES_EVIDENCIA.maxTotalKb * 1024;

    if (total > maximoTotal) {
      contenedorEvidencia.appendChild(aviso('La evidencia suma ' + pesoLegible(total)
        + ' y el máximo es ' + pesoLegible(maximoTotal) + '.'
        + ' Quita algún archivo o sube uno más ligero.'));
    }

    const reducidas = detalle.filter(function (item) { return item.comprimida; });
    if (reducidas.length) {
      const nota = document.createElement('div');
      nota.className = 'alert alert-info py-2 small text-start mb-2';
      nota.textContent = reducidas.length + (reducidas.length === 1
        ? ' imagen se redujo' : ' imágenes se redujeron') + ' para que el envío no se corte.';
      contenedorEvidencia.appendChild(nota);
    }

    const lista = document.createElement('ul');
    lista.className = 'list-unstyled mb-0 text-start';

    detalle.forEach(function (item) {
      const itemContenedor = document.createElement('li');
      itemContenedor.className = 'text-muted small mb-2';

      if (item.comprimida) {
        const reader = new FileReader();
        reader.onload = function (ev) {
          const img = document.createElement('img');
          img.src = ev.target.result;
          img.classList.add('img-fluid', 'rounded', 'mb-1');
          img.style.maxHeight = '300px';
          itemContenedor.insertBefore(img, itemContenedor.firstChild);
        };
        reader.readAsDataURL(item.archivo);
      }

      const texto = document.createElement('div');
      texto.textContent = item.nombre + ' (' + pesoLegible(item.tamano) + ')';
      if (item.comprimida) {
        texto.textContent += ' \u2014 reducido de ' + pesoLegible(item.anterior)
          + ' a ' + pesoLegible(item.tamano) + ' a ' + item.ancho + '\u00d7' + item.alto + ' px';
      }
      itemContenedor.appendChild(texto);
      lista.appendChild(itemContenedor);
    });

    contenedorEvidencia.appendChild(lista);
  }

  inputEvidencia.addEventListener('change', function () {
    contenedorEvidencia.innerHTML = '<div class="text-muted small text-start">Preparando la evidencia...</div>';

    // El compresor reemplaza el FileList del input con DataTransfer. Asignar
    // input.files a mano no dispara 'change', asi que esta funcion no se
    // vuelve a ejecutar sola.
    const originales = Array.from(inputEvidencia.files || []);
    if (!originales.length) {
      return;
    }

    compresionEnCurso = CompresorImagen.procesar(inputEvidencia).then(function (resultado) {
      pintarEvidencia(resultado.detalle);
    }).catch(function () {
      // Si la compresion falla, se envia lo que haya: que decida el servidor.
      pintarEvidencia(originales.map(function (archivo) {
        return { nombre: archivo.name, tamano: archivo.size, anterior: archivo.size,
          comprimida: false, ancho: null, alto: null, archivo: archivo };
      }));
    }).then(function () {
      compresionEnCurso = null;
    });
  });
</script>

{{-- Seccion de envio. Antes este bloque mandaba un correo con EmailJS y despues
     hacia el mismo formulario al servidor, o sea soporte recibia dos correos. Con
     archivos adjuntos EmailJS no sirve de nada, asi que el envio es solo el del
     servidor. --}}
<script>
  const formularioReporte = document.getElementById('reporte_send');
  const modalReporte = document.getElementById('reporte');

  formularioReporte.addEventListener('submit', function (evento) {

    // El editor manda siempre su HTML actual, por si el usuario no escribio nada.
    document.getElementById('descripcion_input').value = editorDescripcion.root.innerHTML;

    // Si todavia se estan reduciendo las imágenes, se espera. Mandar los
    // originales es lo que hace que Vercel corte la peticion con un 413.
    if (compresionEnCurso) {
      evento.preventDefault();

      if (enviarDeNuevo) {
        return;
      }
      enviarDeNuevo = true;

      // El overlay tapa el modal entero, asi que el boton ya no se usa para
      // mostrar el estado. Cada llamada a mostrar reinicia el reloj de 90 s.
      ModalLoader.mostrar(modalReporte, 'Reduciendo imágenes...');

      compresionEnCurso.then(function () {
        enviarDeNuevo = false;
        // requestSubmit() vuelve a disparar este submit, y como la compresion ya
        // termino entra por la rama normal en vez de volver a preventDefault.
        formularioReporte.requestSubmit();
      });

      return;
    }

    ModalLoader.mostrar(modalReporte, 'Enviando reporte...');
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    const descripciones = ['', 'Muy malo', 'Malo', 'Regular', 'Bueno', 'Excelente'];

    document.querySelectorAll('.rating-estrellas').forEach(function(rating){
      const modal = rating.closest('.modal');
      const panel = rating.closest('.rating-panel');
      const hidden = modal.querySelector('input[name="calificacion"]');
      const numEl = panel.querySelector('.calificacion-num');
      const descEl = panel.querySelector('.calificacion-desc');
      let seleccion = parseInt(hidden.value, 10) || 0;

      function colorear(valor){
        rating.querySelectorAll('.estrella').forEach(function(s, i){
          s.style.color = (i < valor) ? '#f5b301' : '#adb5bd';
        });
      }

      function pintarPrevio(valor){
        rating.querySelectorAll('.estrella').forEach(function(s, i){
          s.style.color = (i < valor) ? '#f5b301' : '#adb5bd';
        });
        numEl.textContent = valor + ' / 5';
        descEl.textContent = descripciones[valor] || 'Elige tu calificación';
      }

      function fijar(valor){
        seleccion = valor;
        hidden.value = valor;
        colorear(valor);
        numEl.textContent = valor + ' / 5';
        descEl.textContent = descripciones[valor] || 'Elige tu calificación';
      }

      rating.querySelectorAll('.estrella').forEach(function(estrella){
        estrella.addEventListener('mouseenter', function(){
          pintarPrevio(parseInt(estrella.getAttribute('data-valor'), 10));
        });
        estrella.addEventListener('click', function(){
          fijar(parseInt(estrella.getAttribute('data-valor'), 10));
        });
      });

      panel.addEventListener('mouseleave', function(){
        fijar(seleccion);
      });
    });
  });
</script>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.bg-white.p-4', { opacity: 0, y: 30, duration: 0.6, ease: 'power2.out', delay: 0.3 });
  });
</script>
@endsection
    
