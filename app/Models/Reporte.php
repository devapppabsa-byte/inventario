<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    // 'status' debe estar aqui: sin el, create()/update() lo descartan en
    // silencio y el reporte se queda con status null.
    protected $fillable = ['dispositivo', 'descripcion', 'fecha_reporte', 'fecha_solucion', 'detalles_solucion', 'user_id', 'otro', 'prioridad', 'img', 'calificacion', 'observaciones', 'status'];

    protected $table = 'reportes';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function seguimientos(){
        return $this->hasMany(Seguimiento::class, 'reporte_id');
    }

    /**
     * Evidencia del reporte. La columna `img` sigue existiendo y llenándose para
     * no romper nada de lo anterior, pero la lista de archivos se lee de aquí.
     */
    public function adjuntos(){
        return $this->hasMany(AdjuntoReporte::class, 'reporte_id');
    }

    
}
