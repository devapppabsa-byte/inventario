<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Limpia el HTML que produce el editor de texto enriquecido.
 *
 * Quill manda HTML desde el navegador, así que se guarda tal cual permite
 * inyectar scripts. Como esa descripción la leen después el agente de soporte y
 * el administrador, un `<script>` guardado sería un XSS almacenado capable de
 * ejecutarse en su sesión. Aquí se deja solo el formato.
 */
final class TextoEnriquecido
{
    /**
     * Etiquetas y atributos que Quill puede generar; cualquier otra cosa se
     * descarta. Los atributos van declarados uno a uno porque `HTML.Allowed`
     * con una lista de etiquetas peladas elimina todos los atributos.
     *
     * blockquote, pre y code no aparecen porque la barra del formulario ya no
     * ofrece citas ni bloques de codigo; sin esto pegarlas traeria el formato
     * de vuelta por la puerta de atras.
     */
    private const ETIQUETAS = 'p[style],br,span[style],div[style],strong,b,em,i,u,s,strike,sub,sup,'
        . 'a[href|title|target],ul,ol,li,h1,h2,h3,h4,h5,h6,hr,'
        . 'table,thead,tbody,tr,th[style],td[style]';

    public static function limpiar(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $purificador = new HTMLPurifier(self::configuracion());

        return trim($purificador->purify($html));
    }

    /** El texto sin formato, para decidir si el usuario escribió algo. */
    public static function textoPlano(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/\s+/u', ' ', $texto) ?? '';

        return trim(str_replace("\xC2\xA0", ' ', $texto));
    }

    public static function tieneContenido(?string $html): bool
    {
        return self::textoPlano($html) !== '';
    }

    private static function configuracion(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', self::cacheDir());
        $config->set('HTML.Allowed', self::ETIQUETAS);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.EnableID', false);
        $config->set('HTML.Nofollow', true);
        $config->set('HTML.TargetBlank', true);
        // Los estilos de Quill (color, fondo) se filtran contra la lista propia de
        // HTMLPurifier, que no incluye position ni nada que sirva para tapar la UI.
        $config->set('CSS.Trusted', false);
        // Los href se validan con el filtrado normal de URI: se permiten http,
        // https y mailto, y javascript: o data: se descartan. Desactivar
        // recursos externos borraría también los enlaces que sí son legítimos.
        $config->set('URI.DisableExternalResources', false);

        return $config;
    }

    /** HTMLPurifier necesita un directorio escribible para su cache de definiciones. */
    private static function cacheDir(): string
    {
        $dir = storage_path('app/htmlpurifier');

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return is_dir($dir) ? $dir : sys_get_temp_dir();
    }
}
