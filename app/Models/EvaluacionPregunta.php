<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionPregunta extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_preguntas';

    protected $fillable = ['seccion_id', 'pregunta', 'puntos_maximos', 'orden', 'activo'];

    public function seccion()
    {
        return $this->belongsTo(EvaluacionSeccion::class, 'seccion_id');
    }

    public function opciones()
    {
        return $this->hasMany(EvaluacionOpcion::class, 'pregunta_id')->orderBy('orden');
    }

    public function opcionesActivas()
    {
        return $this->opciones()->where('activo', true);
    }
}
