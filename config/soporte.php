<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Limite de la exportacion de soporte
    |--------------------------------------------------------------------------
    |
    | dompdf se atraganta con miles de filas, asi que si los filtros aplicados
    | devuelven mas solicitudes que esto, se avisa al admin en vez de intentar
    | generar el PDF y dejar la peticion colgada.
    |
    */

    'export_limite' => env('SOPORTE_EXPORT_LIMITE', 1000),

];
