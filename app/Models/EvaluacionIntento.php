<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionIntento extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_intentos';

    protected $fillable = [
        'evaluacion_id', 'usuario_id', 'proveedor_id',
        'fecha_inicio', 'fecha_finalizacion', 'puntuacion',
        'porcentaje', 'estado', 'comentario', 'secciones_permitidas',
    ];

    protected $dates = ['fecha_inicio', 'fecha_finalizacion'];

    protected $casts = [
        'secciones_permitidas' => 'array',
    ];

    const ESTADO_EN_PROGRESO = 'en_progreso';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_CANCELADO = 'cancelado';

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function respuestas()
    {
        return $this->hasMany(EvaluacionRespuesta::class, 'intento_id');
    }

    public function estaEnProgreso()
    {
        return $this->estado === self::ESTADO_EN_PROGRESO;
    }

    public function estaCompletado()
    {
        return $this->estado === self::ESTADO_COMPLETADO;
    }

    public function estaCancelado()
    {
        return $this->estado === self::ESTADO_CANCELADO;
    }

    /**
     * Plazo para completar el intento: la periodicidad mas larga de las
     * subencuetas que incluye, no la de la encuesta (esa ya no existe).
     */
    public function periodoDiasMaximo()
    {
        $secciones = $this->evaluacion->secciones()
            ->whereIn('id', $this->seccionesPermitidasIds())
            ->get();

        $maximo = $secciones->map(fn($s) => (int) $s->periodoDias())->max();

        return $maximo ? (int) $maximo : 30;
    }

    public function esVencido()
    {
        if (!$this->estaEnProgreso() || !$this->fecha_inicio) {
            return false;
        }
        $limite = $this->fecha_inicio->copy()->addDays($this->periodoDiasMaximo());
        return Carbon::now()->gt($limite);
    }

    public function recalcular()
    {
        $this->loadMissing('respuestas.opcion');
        $maximo = 0;
        $obtenido = 0;

        foreach ($this->responderPreguntas() as $pregunta) {
            $maximo += (float) $pregunta->puntos_maximos;
        }

        foreach ($this->respuestas as $respuesta) {
            if ($respuesta->opcion) {
                $obtenido += (float) $respuesta->opcion->puntos;
            }
        }

        $this->puntuacion = round($obtenido, 2);
        $this->porcentaje = $maximo > 0 ? round(($obtenido / $maximo) * 100, 2) : 0;

        return $this;
    }

    public function seccionesPermitidasIds()
    {
        $secciones = $this->secciones_permitidas;
        if (is_array($secciones) && count($secciones) > 0) {
            return array_values(array_map('intval', $secciones));
        }
        // Fallback: todas las secciones activas de la evaluacion
        return $this->evaluacion->seccionesActivas()->pluck('id')->map(fn($id) => (int) $id)->values()->all();
    }

    public function responderPreguntas()
    {
        $seccionIds = $this->seccionesPermitidasIds();
        $preguntas = collect();
        foreach ($this->evaluacion->seccionesActivas()->whereIn('id', $seccionIds)->get() as $seccion) {
            foreach ($seccion->preguntasActivas()->get() as $pregunta) {
                $preguntas->push($pregunta);
            }
        }
        return $preguntas;
    }
}
