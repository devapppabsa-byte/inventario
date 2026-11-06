<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionSeccion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_secciones';

    protected $fillable = ['evaluacion_id', 'titulo', 'descripcion', 'periodicidad', 'orden', 'activo'];

    public function periodoDias()
    {
        switch ($this->periodicidad) {
            case 'mensual':
                return 30;
            case 'bimestral':
                return 60;
            case 'trimestral':
                return 90;
            case 'semestral':
                return 180;
            case 'anual':
                return 365;
            default:
                return 30;
        }
    }

    public function periodicidadLabel()
    {
        return ucfirst($this->periodicidad ?: 'mensual');
    }

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function preguntas()
    {
        return $this->hasMany(EvaluacionPregunta::class, 'seccion_id')->orderBy('orden');
    }

    public function preguntasActivas()
    {
        return $this->preguntas()->where('activo', true);
    }
}
