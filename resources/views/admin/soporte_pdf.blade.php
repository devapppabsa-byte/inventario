<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Solicitudes de Soporte</title>
    <style>
        @page { margin: 1.8cm 1.5cm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #212529;
        }

        h1 { font-size: 17px; margin: 0 0 2px; }
        .sub { font-size: 9px; color: #6c757d; }

        .filtros {
            background: #f1f3f5;
            border-left: 3px solid #343a40;
            padding: 6px 8px;
            margin: 8px 0 12px;
            font-size: 9px;
        }
        .filtros strong { font-size: 9px; }
        .filtros ul { margin: 3px 0 0; padding-left: 16px; }

        .resumen { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .resumen td {
            border: 1px solid #dee2e6;
            text-align: center;
            padding: 6px 4px;
            width: 20%;
        }
        .resumen .valor { font-size: 15px; font-weight: bold; display: block; }
        .resumen .etiqueta { font-size: 8px; color: #6c757d; text-transform: uppercase; }

        .ticket {
            border: 1px solid #dee2e6;
            border-left: 4px solid #343a40;
            padding: 8px 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .ticket.completado { border-left-color: #495057; }
        .ticket.pendiente  { border-left-color: #adb5bd; }

        .ticket-head {
            font-size: 11px;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .estado {
            display: inline-block;
            border: 1px solid #343a40;
            padding: 0 5px;
            font-size: 8px;
            font-weight: normal;
            margin-left: 6px;
        }

        table.datos { width: 100%; border-collapse: collapse; }
        table.datos td {
            padding: 2px 4px 2px 0;
            vertical-align: top;
            font-size: 9px;
        }
        table.datos td.k {
            width: 105px;
            color: #6c757d;
            font-weight: bold;
        }

        .obs {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 5px 6px;
            margin-top: 3px;
            font-size: 9px;
        }

        .seguimiento { margin-top: 7px; }
        .seguimiento .titulo {
            font-size: 8px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: bold;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
            margin-bottom: 3px;
        }
        .seguimiento .nota { font-size: 9px; margin-bottom: 4px; }
        .seguimiento .nota .quien { font-weight: bold; }
        .seguimiento .nota .cuando { color: #6c757d; font-size: 8px; }
        .sin-seguimiento { font-size: 8px; color: #adb5bd; font-style: italic; }

        .sin-datos {
            text-align: center;
            color: #6c757d;
            padding: 30px 0;
            font-size: 10px;
        }
    </style>
</head>
<body>

<h1>Solicitudes de Soporte Técnico</h1>
<div class="sub">Generado el {{$generado}}</div>

<div class="filtros">
    <strong>Filtros aplicados:</strong>
    <ul>
        @foreach ($filtros as $filtro)
            <li>{{$filtro}}</li>
        @endforeach
    </ul>
</div>

<table class="resumen">
    <tr>
        <td><span class="valor">{{$totalReportes}}</span><span class="etiqueta">Solicitudes</span></td>
        <td><span class="valor">{{$pendientes}}</span><span class="etiqueta">Pendientes</span></td>
        <td><span class="valor">{{$completados}}</span><span class="etiqueta">Completados</span></td>
        <td><span class="valor">{{$totalEvaluados}}</span><span class="etiqueta">Calificados</span></td>
        <td><span class="valor">{{$promedio !== null ? $promedio : '—'}}</span><span class="etiqueta">Promedio / 5</span></td>
    </tr>
</table>

@if ($totalReportes == 0)
    <div class="sin-datos">
        No hay solicitudes que cumplan los filtros seleccionados.
    </div>
@endif

@foreach ($reportes as $reporte)
    @php
        $completado = $reporte->status == 'completado';
        $solicitudes = $reporte->user->reportes()->where('id', '!=', $reporte->id)->count();
    @endphp
    <div class="ticket {{$completado ? 'completado' : 'pendiente'}}">
        <div class="ticket-head">
            Solicitud #{{$reporte->id}}
            <span class="estado">{{$completado ? 'COMPLETADO' : 'PENDIENTE'}}</span>
        </div>

        <table class="datos">
            <tr>
                <td class="k">Usuario</td>
                <td>
                    {{$reporte->user->name}}
                    @if ($reporte->user->puesto) — {{$reporte->user->puesto}} @endif
                    @if ($reporte->user->planta) (planta {{$reporte->user->planta}}) @endif
                </td>
            </tr>
            <tr>
                <td class="k">Fecha de reporte</td>
                <td>{{$reporte->fecha_reporte}}</td>
            </tr>
            <tr>
                <td class="k">Dispositivo</td>
                <td>
                    {{$reporte->dispositivo}}
                    @if ($reporte->dispositivo == 'Otro' && $reporte->otro) — {{$reporte->otro}} @endif
                </td>
            </tr>
            <tr>
                <td class="k">Prioridad</td>
                <td>{{$reporte->prioridad}}</td>
            </tr>
            <tr>
                <td class="k">Descripción</td>
                {{-- HTML ya saneado con HTMLPurifier al guardarse --}}
                <td><div class="descripcion-enriquecida">{!! $reporte->descripcion !!}</div></td>
            </tr>
            @if ($reporte->adjuntos->isNotEmpty())
                <tr>
                    <td class="k">Evidencia</td>
                    <td>
                        @foreach ($reporte->adjuntos as $adjunto)
                            <div>{{ $adjunto->nombre_original }} <small>({{ $adjunto->tamanoLegible() }})</small></div>
                        @endforeach
                    </td>
                </tr>
            @endif
            @if ($completado)
                <tr>
                    <td class="k">Fecha de solución</td>
                    <td>{{$reporte->fecha_solucion}}</td>
                </tr>
                @if ($reporte->detalles_solucion && $reporte->detalles_solucion != 'pendiente')
                    <tr>
                        <td class="k">Detalles</td>
                        <td>{{$reporte->detalles_solucion}}</td>
                    </tr>
                @endif
            @endif
            <tr>
                <td class="k">Calificación</td>
                <td>
                    @if ($reporte->calificacion)
                        {{$reporte->calificacion}} / 5
                        @if ($reporte->observaciones)
                            <div class="obs"><strong>Observaciones del usuario:</strong> {{$reporte->observaciones}}</div>
                        @endif
                    @else
                        Sin calificar
                    @endif
                </td>
            </tr>
            <tr>
                <td class="k">Otras solicitudes</td>
                <td>{{$solicitudes}}</td>
            </tr>
        </table>

        <div class="seguimiento">
            <div class="titulo">Seguimiento ({{$reporte->seguimientos->count()}})</div>
            @forelse ($reporte->seguimientos as $nota)
                <div class="nota">
                    <span class="quien">{{$nota->usuario}}</span>
                    <span class="cuando">— {{$nota->created_at ? \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') : $nota->fecha}}</span>
                    <div>{{$nota->comentario}}</div>
                </div>
            @empty
                <div class="sin-seguimiento">Sin comentarios de seguimiento.</div>
            @endforelse
        </div>
    </div>
@endforeach

</body>
</html>
