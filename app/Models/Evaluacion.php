<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluaciones';

    protected $fillable = ['titulo', 'descripcion', 'tipo_usuario', 'activo'];

    public function secciones()
    {
        return $this->hasMany(EvaluacionSeccion::class, 'evaluacion_id')->orderBy('orden');
    }

    public function seccionesActivas()
    {
        return $this->secciones()->where('activo', true);
    }

    public function proveedores()
    {
        return $this->hasMany(EvaluacionProveedor::class, 'evaluacion_id');
    }

    public function proveedoresActivos()
    {
        return $this->proveedores()->where('activo', true);
    }

    public function intentos()
    {
        return $this->hasMany(EvaluacionIntento::class, 'evaluacion_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'evaluacion_usuario', 'evaluacion_id', 'usuario_id');
    }

    public function proveedoresModel()
    {
        return $this->belongsToMany(Proveedor::class, 'evaluacion_proveedores', 'evaluacion_id', 'proveedor_id');
    }
}
