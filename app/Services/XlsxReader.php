<?php
namespace App\Services;

use ZipArchive;
use SimpleXMLElement;
use RuntimeException;

class XlsxReader
{
    private const NS_P  = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /** @return array<int, array<int, string>> filas 1-indexadas: [nFilas => [nCol => valor]] */
    public static function leer(string $ruta): array
    {
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new RuntimeException('El archivo .xlsx no se pudo abrir (¿está dañado o no es un Excel?).');
        }
        try {
            $strings = self::stringsCompartidas($zip);
            $hoja    = self::primeraHoja($zip);

            $filas = [];
            foreach ($hoja->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $fila) {
                $num = (int) $fila['r'];
                $celdas = [];
                foreach ($fila->xpath('./*[local-name()="c"]') ?: [] as $c) {
                    $ref    = (string) $c['r'];
                    $col    = self::columna($ref);
                    $tipo   = (string) $c['t'];
                    $v      = $c->xpath('./*[local-name()="v"]');
                    if ($tipo === 's') {
                        $valor = $strings[(int) ($v[0] ?? -1)] ?? '';
                    } elseif ($tipo === 'inlineStr') {
                        $is = $c->xpath('./*[local-name()="is"]/*[local-name()="t"]');
                        $valor = (string) ($is[0] ?? '');
                    } else {
                        $valor = (string) ($v[0] ?? '');
                    }
                    $celdas[$col] = $valor;
                }
                $filas[$num] = $celdas;
            }
            return $filas;
        } finally {
            $zip->close();
        }
    }

    private static function stringsCompartidas(ZipArchive $zip): array
    {
        $xml = self::xmlDe($zip, 'xl/sharedStrings.xml');
        if (!$xml) return [];

        $strings = [];
        foreach ($xml->xpath('//*[local-name()="si"]') ?: [] as $si) {
            $texto = '';
            foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                $texto .= (string) $t;
            }
            // el texto puede estar repartido en varios <t> una sola vez
            $strings[] = $texto;
        }
        return $strings;
    }

    private static function primeraHoja(ZipArchive $zip): SimpleXMLElement
    {
        foreach (['xl/worksheets/sheet1.xml'] as $p) {
            $xml = self::xmlDe($zip, $p);
            if ($xml) return $xml;
        }

        // Fallback: resolver r:id de la primera hoja vía workbook.xml.rels
        $wb   = self::xmlDe($zip, 'xl/workbook.xml');
        $rels = self::xmlDe($zip, 'xl/_rels/workbook.xml.rels');
        if ($wb && $rels) {
            $rid = (string) ($wb->xpath('//*[local-name()="sheet"][1]/@r:id')[0] ?? '');
            if ($rid) {
                $target = (string) ($rels->xpath('//*[@Id="' . $rid . '"]/@Target')[0] ?? '');
                if ($target) {
                    $xml = self::xmlDe($zip, 'xl/' . ltrim($target, '/'));
                    if ($xml) return $xml;
                }
            }
        }
        throw new RuntimeException('No se encontró la hoja de datos en el Excel.');
    }

    private static function xmlDe(ZipArchive $zip, string $p): ?SimpleXMLElement
    {
        $contenido = $zip->getFromName($p);
        if ($contenido === false) return null;

        $previo = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contenido);
        libxml_use_internal_errors($previo);
        return $xml === false ? null : $xml;
    }

    private static function columna(string $ref): int
    {
        preg_match('/^[A-Z]+/', $ref, $m);
        $letras = $m[0] ?? 'A';
        $num = 0;
        foreach (str_split($letras) as $l) {
            $num = $num * 26 + (ord($l) - 64);
        }
        return $num;
    }
}
