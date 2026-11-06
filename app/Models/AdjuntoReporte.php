<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Un archivo de evidencia de un reporte de soporte.
 *
 * La ruta se guarda relativa al disco 'local' (storage/app). No se usa el nombre
 * que envía el cliente para construir la ruta en disco, solo para mostrarse.
 */
class AdjuntoReporte extends Model
{
    use HasFactory;

    protected $fillable = ['reporte_id', 'ruta', 'nombre_original', 'mime', 'tamano', 'es_imagen'];

    // El nombre de la tabla no sigue la convencion que Eloquent deduce del
    // nombre de la clase (que seria "adjunto_reportes"), asi que va explicito.
    protected $table = 'reporte_adjuntos';

    protected $casts = [
        'es_imagen' => 'boolean',
        'tamano'   => 'integer',
    ];

    public function reporte()
    {
        return $this->belongsTo(Reporte::class);
    }

    public function existeEnDisco(): bool
    {
        return $this->ruta !== '' && Storage::disk('local')->exists($this->ruta);
    }

    public function rutaAbsoluta(): string
    {
        return Storage::disk('local')->path($this->ruta);
    }

    public function icono(): string
    {
        $mime = (string) $this->mime;

        if (str_starts_with($mime, 'image/')) {
            return 'fa-file-image';
        }
        if (str_contains($mime, 'pdf')) {
            return 'fa-file-pdf';
        }
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) {
            return 'fa-file-word';
        }
        if (str_contains($mime, 'sheet') || str_contains($mime, 'excel') || str_contains($mime, 'csv')) {
            return 'fa-file-excel';
        }
        if (str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint')) {
            return 'fa-file-powerpoint';
        }

        return 'fa-file';
    }

    public function tamanoLegible(): string
    {
        $bytes = (int) $this->tamano;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
