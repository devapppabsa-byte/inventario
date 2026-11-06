<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AgenteSoporte extends Authenticatable
{
    protected $table = 'soporte_agentes';

    protected $fillable = ['nombre', 'email', 'password', 'activo'];

    protected $hidden = ['password'];

    protected $casts = ['activo' => 'boolean'];

    public function estaActivo()
    {
        return (bool) $this->activo;
    }
}
