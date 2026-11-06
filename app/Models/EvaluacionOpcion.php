<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionOpcion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_opciones';

    protected $fillable = ['pregunta_id', 'texto', 'puntos', 'valor', 'orden', 'activo'];

    public function pregunta()
    {
        return $this->belongsTo(EvaluacionPregunta::class, 'pregunta_id');
    }
}
