<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Nunito', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #dc3545; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 25px; }
        .info-row { margin-bottom: 12px; }
        .info-row label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 2px; }
        .info-row span { font-size: 15px; color: #222; }
        .prioridad-alta { color: #dc3545; font-weight: bold; }
        .prioridad-media { color: #fd7e14; font-weight: bold; }
        .prioridad-baja { color: #198754; font-weight: bold; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-laptop-medical"></i> Nueva Solicitud de Soporte Técnico</h1>
        </div>
        <div class="body">
            <div class="info-row">
                <label>Usuario</label>
                <span>{{$reporte->user->name}}</span>
            </div>
            <div class="info-row">
                <label>Correo</label>
                <span>{{$reporte->user->email}}</span>
            </div>
            <div class="info-row">
                <label>Dispositivo</label>
                <span>{{$reporte->dispositivo}} {{$reporte->otro ? ' - '.$reporte->otro : ''}}</span>
            </div>
            <div class="info-row">
                <label>Descripción</label>
                <span>{{$reporte->descripcion}}</span>
            </div>
            <div class="info-row">
                <label>Prioridad</label>
                <span class="prioridad-{{$reporte->prioridad}}">{{ucfirst($reporte->prioridad)}}</span>
            </div>
            <div class="info-row">
                <label>Fecha</label>
                <span>{{$reporte->fecha_reporte}}</span>
            </div>
            <div class="info-row">
                <label>Captura del problema</label>
                <img src="{{ $message->embed($foto_path) }}" alt="Captura del problema" style="max-width:100%; border-radius:6px;">
            </div>
        </div>
        <div class="footer">
            Sistema de Inventario de TI — Grupo PABSA
        </div>
    </div>
</body>
</html>
