<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'descripcion', 'contacto', 'correo', 'telefono', 'activo'];

    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionProveedor::class, 'proveedor_id');
    }

    public function intentos()
    {
        return $this->hasMany(EvaluacionIntento::class, 'proveedor_id');
    }
}
