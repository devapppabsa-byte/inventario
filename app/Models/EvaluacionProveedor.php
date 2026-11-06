<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionProveedor extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_proveedores';

    protected $fillable = ['evaluacion_id', 'proveedor_id', 'activo'];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
