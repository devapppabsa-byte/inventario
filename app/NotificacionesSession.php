<?php

namespace App;

class NotificacionesSession
{
    /**
     * Traduce las claves de session con contenido a la lista que consume
     * public/js/toastr-notificaciones.js.
     *
     * Solo se devuelven las claves mapeadas en config/notificaciones.php, de
     * modo que una clave sin mapear sigue mostrandose en su bloque en linea y
     * nunca se pierde. Recorre el mapa en orden, asi que el tope "limite"
     * descarta por orden de aparicion en el mapa y no por antiguedad del
     * mensaje, que es lo unico que la session conserva tras el redirect.
     *
     * @return array<int, array{texto: string, tipo: string}>
     */
    public static function paraElToast(): array
    {
        $limite = (int) config('notificaciones.limite', 5);
        $notificaciones = [];

        foreach ((array) config('notificaciones.mapa', []) as $clave => $tipo) {

            if (count($notificaciones) >= $limite) {
                break;
            }

            $valor = session($clave);

            /* Un session() con array se aplana para no romper el JSON. */
            if (is_array($valor)) {
                $valor = implode(' ', array_filter($valor, 'is_scalar'));
            }

            if (! is_scalar($valor)) {
                continue;
            }

            $valor = trim((string) $valor);

            if ($valor === '') {
                continue;
            }

            $notificaciones[] = [
                'texto' => $valor,
                'tipo'  => (string) $tipo,
            ];
        }

        return $notificaciones;
    }
}
