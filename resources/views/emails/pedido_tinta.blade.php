<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Nunito', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #6f42c1; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 25px; }
        .info-row { margin-bottom: 12px; }
        .info-row label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 2px; }
        .info-row span { font-size: 15px; color: #222; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-palette"></i> Nuevo Pedido de Tintas</h1>
        </div>
        <div class="body">
            <div class="info-row">
                <label>Usuario</label>
                <span>{{$pedido->user->name}}</span>
            </div>
            <div class="info-row">
                <label>Correo</label>
                <span>{{$pedido->user->email}}</span>
            </div>
            <div class="info-row">
                <label>Impresora</label>
                <span>{{$pedido->numero}}</span>
            </div>
            @if ($pedido->marca)
            <div class="info-row">
                <label>Marca</label>
                <span>{{$pedido->marca}}</span>
            </div>
            @endif
            <div class="info-row">
                <label>Colores</label>
                <span>{{implode(', ', json_decode($pedido->colores, true))}}</span>
            </div>
            <div class="info-row">
                <label>Fecha</label>
                <span>{{$pedido->fecha_pedido}}</span>
            </div>
            <div class="info-row">
                <label>Foto del nivel de tinta</label>
                <img src="{{ $message->embed($foto_path) }}" alt="Foto de tintas" style="max-width:100%; border-radius:6px;">
            </div>
        </div>
        <div class="footer">
            Sistema de Inventario de TI — Arturo Resendiz
        </div>
    </div>
</body>
</html>
