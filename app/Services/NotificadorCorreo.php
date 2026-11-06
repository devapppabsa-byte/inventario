<?php

namespace App\Services;

use App\Mail\PedidoTinta;
use App\Mail\ReporteSoporte;
use App\Models\CorreoEnvio;
use App\Models\EmailSetting;
use App\Models\Pedido;
use App\Models\Reporte;
use Illuminate\Support\Facades\Mail;

class NotificadorCorreo
{
    /**
     * Notifica una solicitud de soporte.
     *
     * El reporte ya esta guardado y con sus adjuntos cuando entra aca, asi que
     * un fallo de SMTP no puede convertirse en un error de peticion: se registra
     * y se sigue, para que el usuario no repita el reporte y duplique el ticket.
     *
     * @return array{ok:bool,error:?string,intentos:int}
     */
    public static function soporte(Reporte $reporte): array
    {
        $setting = EmailSetting::obtener('soporte');

        if (! $setting) {
            return ['ok' => false, 'error' => 'No hay configuracion de correo para soporte.', 'intentos' => 0];
        }

        // El usuario viene cargado en el reporte; si se borro despues, el
        // helper simplemente no lo suma a la copia.
        $reporte->loadMissing('user');
        $copias = EmailSetting::copias($setting, $reporte->user);
        $destinatarios = array_merge([$setting->destinatario], $copias);

        $registro = new CorreoEnvio([
            'enviable_type' => $reporte->getMorphClass(),
            'enviable_id'   => $reporte->getKey(),
        ]);

        return $registro->intentar('soporte', $destinatarios, function () use ($setting, $copias, $reporte) {
            $envio = Mail::to($setting->destinatario);
            if ($copias) {
                $envio->cc($copias);
            }
            $envio->send(new ReporteSoporte($reporte, $setting->mail_from, $setting->mail_from_name));
        });
    }

    /**
     * Notifica un pedido de tintas.
     *
     * @return array{ok:bool,error:?string,intentos:int}
     */
    public static function tinta(Pedido $pedido): array
    {
        $setting = EmailSetting::obtener('tintas');

        if (! $setting) {
            return ['ok' => false, 'error' => 'No hay configuracion de correo para tintas.', 'intentos' => 0];
        }

        $pedido->loadMissing('user');
        $copias = EmailSetting::copias($setting, $pedido->user);
        $destinatarios = array_merge([$setting->destinatario], $copias);

        $registro = new CorreoEnvio([
            'enviable_type' => $pedido->getMorphClass(),
            'enviable_id'   => $pedido->getKey(),
        ]);

        return $registro->intentar('tintas', $destinatarios, function () use ($setting, $copias, $pedido) {
            // Un solo envio: la version anterior mandaba dos correos con el
            // mismo pedido, el primero sin copia y el segundo con la copia.
            $envio = Mail::to($setting->destinatario);
            if ($copias) {
                $envio->cc($copias);
            }
            $envio->send(new PedidoTinta($pedido, $setting->mail_from, $setting->mail_from_name));
        });
    }
}
