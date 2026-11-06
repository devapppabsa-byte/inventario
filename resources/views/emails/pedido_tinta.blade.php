<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Solicitud de tinta #{{ $pedido->id }}</title>
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
@php
    $tipoInsumo = $pedido->numero == 'Cintas' ? 'cintas para b&aacute;scula' : 'la impresora ' . e($pedido->numero);
@endphp
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
    {{ $pedido->user->name }} solicita {{ $tipoInsumo }}.{{ $pedido->parent_id ? ' Es un pedido residual del pedido #' . $pedido->parent_id . '.' : '' }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f2f2f2;">
    <tr>
        <td align="center" style="padding:28px 12px;">

            <table role="presentation" class="contenedor" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background:#ffffff;border:1px solid #e5e5e5;border-radius:10px;">

                {{-- Encabezado: banda negra, titulo a la izquierda y numero de
                     solicitud a la derecha. --}}
                <tr>
                    <td class="celda" style="background:#111111;padding:26px 32px;border-radius:10px 10px 0 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div style="font-size:11px;color:#8a8a8a;text-transform:uppercase;letter-spacing:0.14em;font-weight:600;padding-bottom:6px;">
                                        Recepci&oacute;n de tintas
                                    </div>
                                    <div style="font-size:21px;color:#ffffff;font-weight:700;letter-spacing:-0.01em;">
                                        Nueva solicitud de tintas
                                    </div>
                                </td>
                                <td align="right" style="vertical-align:middle;width:1%;padding-left:16px;">
                                    <span style="display:inline-block;background:#2a2a2a;color:#ffffff;font-family:Menlo,Consolas,monospace;font-size:12px;font-weight:600;padding:6px 11px;border-radius:6px;white-space:nowrap;">
                                        #{{ $pedido->id }}
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

                        @if ($pedido->parent_id)
                            <div style="background:#fff8e1;border:1px solid #f0d060;border-radius:8px;padding:12px 16px;font-size:14px;color:#5a4a00;margin-bottom:18px;">
                                <strong>Pedido residual del pedido #{{ $pedido->parent_id }}:</strong>
                                solo incluye las tintas que quedaron pendientes de entregar.
                            </div>
                        @endif

                        {{-- Datos del pedido en dos columnas: etiqueta gris, valor
                             negro. --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="dato-label" width="40%">Usuario</td>
                                <td class="dato-valor">{{ $pedido->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">Correo</td>
                                <td class="dato-valor">{{ $pedido->user->correo }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">{{ $pedido->numero == 'Cintas' ? 'Tipo de insumo' : 'N&uacute;mero de impresora' }}</td>
                                <td class="dato-valor">
                                    {{ $pedido->numero == 'Cintas' ? 'Cintas para impresora de b&aacute;scula' : $pedido->numero }}
                                </td>
                            </tr>
                            @if ($pedido->marca)
                                <tr>
                                    <td class="dato-label">Marca</td>
                                    <td class="dato-valor">{{ $pedido->marca }}</td>
                                </tr>
                            @endif
                            @php
                                $insumos = json_decode($pedido->colores, true);
                                $etiquetaInsumos = $pedido->numero == 'Cintas' ? 'Cantidad' : 'Colores';
                                $insumosTexto = is_array($insumos)
                                    ? implode(', ', $insumos)
                                    : trim((string) $pedido->colores);
                            @endphp
                            <tr>
                                <td class="dato-label">{{ $etiquetaInsumos }}</td>
                                <td class="dato-valor">{{ $insumosTexto }}</td>
                            </tr>
                            <tr>
                                <td class="dato-label">Fecha de solicitud</td>
                                <td class="dato-valor">{{ $pedido->fecha_pedido }}</td>
                            </tr>
                        </table>

                        <div style="font-size:11px;color:#737373;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;padding:30px 0 12px 0;">
                            Foto del nivel de tinta
                        </div>
                        <div class="evidencia">
                            <img src="{{ $message->embed($foto_path) }}" alt="Foto del nivel de tinta">
                        </div>

                    </td>
                </tr>

                <tr>
                    <td class="celda" style="background:#f7f7f7;border-top:1px solid #e5e5e5;padding:18px 32px;border-radius:0 0 10px 10px;">
                        <div style="font-size:12px;color:#8a8a8a;line-height:1.6;text-align:center;">
                            Sistema de Inventario de TI &mdash; Grupo PABSA<br>
                            Responde a este correo para dar seguimiento a la solicitud #{{ $pedido->id }}.
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>