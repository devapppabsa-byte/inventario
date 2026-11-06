<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Solicitud de soporte #{{ $reporte->id }}</title>
    <style>
        /* En correo no se usa flex ni grid: Outlook no los soporta. Todo va en
           tablas con rol de presentacion, y el color en el style de cada celda
           porque el bloque <style> lo descartan varios clientes. */
        body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table { border-collapse: collapse; }

        .dato-label {
            font-size: 11px;
            color: #737373;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            padding: 6px 16px 6px 0;
            white-space: nowrap;
            vertical-align: top;
        }

        .dato-valor {
            font-size: 15px;
            color: #1a1a1a;
            padding: 6px 0;
        }

        .descripcion p { margin: 0 0 10px 0; }
        .descripcion > *:last-child { margin-bottom: 0; }
        .descripcion ul, .descripcion ol { margin: 0 0 10px 0; padding-left: 22px; }
        .descripcion h2 { font-size: 17px; margin: 14px 0 6px 0; }
        .descripcion h3 { font-size: 15px; margin: 12px 0 6px 0; }
        .descripcion a { color: #1a1a1a; }
        .descripcion table { width: 100%; }
        .descripcion td, .descripcion th { border: 1px solid #d4d4d4; padding: 4px 8px; }

        .evidencia img {
            display: block;
            max-width: 100%;
            width: 100%;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
        }

        .nota td {
            background: #f2f2f2;
            border-left: 3px solid #111111;
        }

        @media only screen and (max-width: 620px) {
            .contenedor { width: 100% !important; }
            .celda { padding-left: 20px !important; padding-right: 20px !important; }
            .dato-label, .dato-valor { display: block; width: auto; padding-left: 0; }
            .dato-label { padding-top: 10px; }
        }
    </style>
</head>
<body>

{{-- Vista previa en la bandeja: sin esto los clientes muestran "..." porque el
     primer texto visible del correo esta varias lineas mas abajo. --}}
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
    {{ $reporte->user->name }} reporta una falla en
    {{ $reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo }},
    prioridad {{ strtolower($reporte->prioridad) }}.
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f2f2f2;">
    <tr>
        <td align="center" style="padding:28px 12px;">

            <table role="presentation" class="contenedor" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background:#ffffff;border:1px solid #e5e5e5;border-radius:10px;">

                {{-- Encabezado: banda negra, titulo a la izquierda y numero de
                     ticket a la derecha. --}}
                <tr>
                    <td class="celda" style="background:#111111;padding:26px 32px;border-radius:10px 10px 0 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div style="font-size:11px;color:#8a8a8a;text-transform:uppercase;letter-spacing:0.14em;font-weight:600;padding-bottom:6px;">
                                        Soporte t&eacute;cnico
                                    </div>
                                    <div style="font-size:21px;color:#ffffff;font-weight:700;letter-spacing:-0.01em;">
                                        Nueva solicitud de soporte
                                    </div>
                                </td>
                                <td align="right" style="vertical-align:middle;width:1%;padding-left:16px;">
                                    <span style="display:inline-block;background:#2a2a2a;color:#ffffff;font-family:Menlo,Consolas,monospace;font-size:12px;font-weight:600;padding:6px 11px;border-radius:6px;white-space:nowrap;">
                                        #{{ $reporte->id }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Filete gris que separa el encabezado del cuerpo. --}}
                <tr>
                    <td style="height:4px;background:#e5e5e5;font-size:0;line-height:0;">&nbsp;</td>
                </tr>

                <tr>
                    <td class="celda" style="padding:30px 32px;">

                        {{-- Datos del reporte en dos columnas: etiqueta gris, valor
                             negro. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="dato-label" width="40%">Usuario</td>
                                <td class="dato-valor">{{ $reporte->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">Correo</td>
                                <td class="dato-valor">{{ $reporte->user->correo }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">Dispositivo</td>
                                <td class="dato-valor">
                                    {{ $reporte->dispositivo == 'Otro' ? $reporte->otro : $reporte->dispositivo }}
                                </td>
                            </tr>
                            <tr>
                                <td class="dato-label">Fecha</td>
                                <td class="dato-valor">{{ $reporte->fecha_reporte }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">Prioridad</td>
                                <td class="dato-valor">
                                    @php
                                        // Monocromatico: el orden se lee por contraste,
                                        // de blanco sobre negro a negro sobre gris claro.
                                        $prioridad = strtolower((string) $reporte->prioridad);
                                        $chips = [
                                            'alta'  => ['fondo' => '#111111', 'texto' => '#ffffff'],
                                            'media' => ['fondo' => '#525252', 'texto' => '#ffffff'],
                                            'baja'  => ['fondo' => '#e5e5e5', 'texto' => '#333333'],
                                        ];
                                        $chip = $chips[$prioridad] ?? ['fondo' => '#e5e5e5', 'texto' => '#333333'];
                                    @endphp
                                    <span style="display:inline-block;background:{{ $chip['fondo'] }};color:{{ $chip['texto'] }};font-size:12px;font-weight:600;letter-spacing:0.04em;padding:4px 12px;border-radius:999px;">
                                        {{ ucfirst($reporte->prioridad) }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        {{-- Descripcion sobre panel gris con filete negro: la separa del
                             bloque de datos sin meter color. El HTML va sin escapar a
                             proposito para que se vea con negritas y listas; ya viene
                             saneado por TextoEnriquecido (HTMLPurifier) al guardarse. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="margin-top:26px;background:#f7f7f7;border-left:3px solid #111111;">
                            <tr>
                                <td style="padding:18px 22px;">
                                    <div style="font-size:11px;color:#737373;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;padding-bottom:10px;">
                                        Descripci&oacute;n de la falla
                                    </div>
                                    <div class="descripcion" style="font-size:15px;color:#1a1a1a;line-height:1.6;">
                                        {!! $reporte->descripcion !!}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        @php
                            $totalAdjuntos = $reporte->adjuntos->count();
                            $imagenes = $reporte->adjuntos->filter(fn($a) => $a->es_imagen && $a->existeEnDisco());
                            $documentos = $reporte->adjuntos->filter(fn($a) => !$a->es_imagen && $a->existeEnDisco());
                            $noDisponibles = $reporte->adjuntos->reject(fn($a) => $a->existeEnDisco());
                        @endphp

                        <div style="font-size:11px;color:#737373;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;padding:30px 0 12px 0;">
                            Evidencia
                            @if($totalAdjuntos > 0)
                                <span style="color:#8a8a8a;text-transform:none;letter-spacing:0;font-weight:400;">
                                    ({{ $totalAdjuntos }} archivo{{ $totalAdjuntos === 1 ? '' : 's' }})
                                </span>
                            @endif
                        </div>

                        @if($totalAdjuntos === 0)
                            <div style="font-size:14px;color:#8a8a8a;">
                                El usuario no adjunt&oacute; archivos.
                            </div>
                        @endif

                        @foreach($imagenes as $imagen)
                            <div class="evidencia" style="padding-bottom:14px;">
                                <img src="{{ $message->embed($imagen->rutaAbsoluta()) }}" alt="Evidencia">
                            </div>
                        @endforeach

                        @if($documentos->isNotEmpty())
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="border:1px solid #e5e5e5;border-radius:6px;">
                                @foreach($documentos as $documento)
                                    <tr>
                                        <td style="padding:11px 16px;border-bottom:1px solid #f2f2f2;font-size:14px;color:#1a1a1a;">
                                            {{ $documento->nombre_original }}
                                        </td>
                                        <td align="right" width="1%" style="padding:11px 16px;border-bottom:1px solid #f2f2f2;font-size:13px;color:#8a8a8a;white-space:nowrap;">
                                            {{ $documento->tamanoLegible() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        @if($noDisponibles->isNotEmpty())
                            <table role="presentation" class="nota" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;">
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <div style="font-size:13px;color:#525252;line-height:1.5;">
                                            <strong style="color:#1a1a1a;">Nota:</strong>
                                            estos archivos figuran en el reporte pero ya no est&aacute;n en el servidor:
                                            @foreach($noDisponibles as $perdido)
                                                {{ $perdido->nombre_original }}@if(!$loop->last),@endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if($totalAdjuntos > 0 && $reporte->adjuntos->contains(fn($a) => $a->existeEnDisco()))
                            <div style="font-size:12px;color:#8a8a8a;padding-top:12px;">
                                Todos los archivos vienen adjuntos a este correo.
                            </div>
                        @endif

                    </td>
                </tr>

                <tr>
                    <td class="celda" style="background:#f7f7f7;border-top:1px solid #e5e5e5;padding:18px 32px;border-radius:0 0 10px 10px;">
                        <div style="font-size:12px;color:#8a8a8a;line-height:1.6;text-align:center;">
                            Sistema de Inventario de TI &mdash; Grupo PABSA<br>
                            Responde a este correo para dar seguimiento al ticket #{{ $reporte->id }}.
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
