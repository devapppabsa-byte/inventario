<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionRespuesta extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_respuestas';

    protected $fillable = ['intento_id', 'pregunta_id', 'opcion_id', 'puntos_obtenidos'];

    public function intento()
    {
        return $this->belongsTo(EvaluacionIntento::class, 'intento_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(EvaluacionPregunta::class, 'pregunta_id');
    }

    public function opcion()
    {
        return $this->belongsTo(EvaluacionOpcion::class, 'opcion_id');
    }
}
