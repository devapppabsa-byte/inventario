<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Genera archivos .xlsx mínimos (una hoja, solo texto) sin dependencias externas.
 *
 * Es el espejo de XlsxReader: el lector abre el .xlsx como ZIP y lee el XML de
 * la hoja, así que aquí se escriben únicamente las partes que eso necesita. Las
 * celdas van como inlineStr para no depender de sharedStrings.xml.
 */
class XlsxWriter
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL  = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @param array<int, array<int, string>> $filas 1-indexadas por posición: [[...celdas], ...]
     * @return string bytes del .xlsx
     */
    public static function generar(array $filas, string $nombreHoja = 'Hoja1'): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($tmp === false) {
            throw new RuntimeException('No se pudo crear el archivo temporal para el Excel.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new RuntimeException('No se pudo crear el archivo .xlsx.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', self::contentTypes());
            $zip->addFromString('_rels/.rels', self::relsRaiz());
            $zip->addFromString('xl/workbook.xml', self::workbook($nombreHoja));
            $zip->addFromString('xl/_rels/workbook.xml.rels', self::relsWorkbook());
            $zip->addFromString('xl/worksheets/sheet1.xml', self::hoja($filas));
        } finally {
            $zip->close();
        }

        $bytes = file_get_contents($tmp);
        @unlink($tmp);

        if ($bytes === false) {
            throw new RuntimeException('No se pudo leer el archivo .xlsx generado.');
        }

        return $bytes;
    }

    private static function hoja(array $filas): string
    {
        $xml = '';
        foreach ($filas as $r => $fila) {
            $celdas = '';
            foreach (array_values($fila) as $c => $valor) {
                $celdas .= sprintf(
                    '<c r="%s%d" t="inlineStr"><is><t>%s</t></is></c>',
                    self::letraColumna($c),
                    $r + 1,
                    htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES)
                );
            }
            $xml .= '<row r="' . ($r + 1) . '">' . $celdas . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="' . self::NS_MAIN . '">'
            . '<sheetData>' . $xml . '</sheetData>'
            . '</worksheet>';
    }

    private static function workbook(string $nombreHoja): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="' . self::NS_MAIN . '" xmlns:r="' . self::NS_REL . '">'
            . '<sheets><sheet name="' . htmlspecialchars($nombreHoja, ENT_XML1 | ENT_QUOTES) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private static function relsRaiz(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . self::NS_REL . '/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function relsWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . self::NS_REL . '/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }

    /** 0 = A, 25 = Z, 26 = AA */
    private static function letraColumna(int $indice): string
    {
        $letras = '';
        for ($i = $indice + 1; $i > 0; $i = intdiv($i - 1, 26)) {
            $letras = chr(65 + (($i - 1) % 26)) . $letras;
        }

        return $letras;
    }
}
