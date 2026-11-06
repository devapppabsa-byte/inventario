<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Genera PDFs a partir de una vista Blade.
 *
 * Se usa dompdf directo (y no barryvdh/laravel-dompdf) porque la version 2.x de
 * ese wrapper arrastra dompdf 2.0.x, que tiene avisos de seguridad, y la 3.x ya
 * exige Laravel 9. Este proyecto es Laravel 8.
 */
class PdfGenerator
{
    /**
     * @param  string  $html  HTML ya renderizado
     * @param  string  $nombre  nombre del archivo, p. ej. soporte_20260928.pdf
     * @param  string  $orientacion  portrait o landscape
     */
    public static function download($html, $nombre, $orientacion = 'portrait')
    {
        $dompdf = new Dompdf(self::opciones());
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', $orientacion);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    private static function opciones()
    {
        $opciones = new Options();
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('isHtml5ParserEnabled', true);
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('chroot', public_path());

        return $opciones;
    }
}
