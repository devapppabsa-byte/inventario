<?php

namespace App\Models;

use App\Models\Visita;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'planta',
        'puesto',
        'extension',
        'celular',
        'jefe',
        'resguardo_firmado',
        'aspel',
        'camaras',
        'soporte',
        'telefonia',
        'correo',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];




//Poniendo de hijas a las demas tablas

public function computadoras(){
    return $this->hasMany(Computadora::class);
}


public function impresoras(){
    return $this->hasMany(Impresora::class);
}


public function telefonos(){
    return $this->hasMany(Telefono::class);
}

public function pedidos(){
    return $this->hasMany(Pedido::class);
}


public function reportes(){
    return $this->hasMany(Reporte::class);
}

public function resguardos(){
    return $this->hasMany(Resguardo::class);
}

public function accesos(){
    return $this->hasMany(Acceso::class);
}


public function comentarios(){
    return  $this->hasMany(Comentario::class);
}

public function reacciones(){
    return $this->hasMany(Reaccion::class);
}

public function visitas(){
    return $this->hasMany(Visita::class);
}


public function sentMessages(){
    return $this->hasMany(Message::class, 'sender_id');
}



public function receivedMessages(){

    return $this->hasMany(Message::class, 'receiver_id');

}

public function evaluaciones(){
    return $this->belongsToMany(Evaluacion::class, 'evaluacion_usuario', 'usuario_id', 'evaluacion_id')
                ->withPivot('activo', 'secciones')
                ->wherePivot('activo', true);
}

public function intentosEvaluacion(){
    return $this->hasMany(EvaluacionIntento::class, 'usuario_id');
}

public function asistenteMensajes(){
    return $this->hasMany(AsistenteMensaje::class);
}






}
