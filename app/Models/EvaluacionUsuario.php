<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionUsuario extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_usuario';

    protected $fillable = ['evaluacion_id', 'usuario_id', 'activo', 'secciones'];

    protected $casts = [
        'secciones' => 'array',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
