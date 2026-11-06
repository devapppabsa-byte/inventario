<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Mapa de claves de session a tipos de toastr
    |---------------------------------------------------------------------------
    |
    | Solo las claves listadas en "mapa" se convierten en notificaciones toastr.
    | El resto de claves de session sigue mostrandose en su bloque en linea
    | original de cada vista, asi que nada se pierde aunque una clave falte.
    |
    | Para silenciar todos los toasts basta con vaciar el mapa; los bloques en
    | linea siguen funcionando y no hay que tocar las 28 vistas.
    |
    */

    'mapa' => [

        /* Correctos */
        'acceso_autorizado'      => 'success',
        'acceso_eliminado'       => 'success',
        'actualizado'            => 'success',
        'agregada'               => 'success',
        'agregado'               => 'success',
        'comentado'              => 'success',
        'completado'             => 'success',
        'config_guardada'        => 'success',
        'editado'                => 'success',
        'eliminado'              => 'success',
        'fotos_subidas'          => 'success',
        'ok'                     => 'success',
        'pedido_enviado'         => 'success',
        'publicacion_agregada'   => 'success',
        'reenviado'              => 'success',
        'respuesta'              => 'success',
        'sitio_autorizado'       => 'success',
        'software_autorizado'    => 'success',

        /* Fallos */
        'acceso_desautorizado'   => 'error',
        'correo_fallido'         => 'error',
        'error'                  => 'error',
        'reenvio_fallido'        => 'error',
        'sitio_desautorizado'    => 'error',
        'software_desautorizado' => 'error',

        /* Informativos */
        'aceptado'               => 'info',
        'anticipado'             => 'info',
        'aut_sitio'              => 'info',
        'info'                   => 'info',
        'observaciones'          => 'info',
        'reportado'              => 'info',
        'solicitado'             => 'info',

    ],

    /*
    |---------------------------------------------------------------------------
    | Tope de toasts simultaneos
    |---------------------------------------------------------------------------
    |
    | Los mensajes de session siguen ademas en su bloque en linea, asi que en una
    | vista con tres avisos se verian tres toasts mas los tres bloques. Este
    | tope evita que el toast se coma media pantalla.
    |
    */

    'limite' => 5,

];
