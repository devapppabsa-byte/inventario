<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    use HasFactory;

    protected $table = 'telefonos';
    protected $fillable = ['marca','modelo', 'serie', 'imagen1','user_id', 'estado', 'tipo','comparte', 'imagen2', 'imagen3', 'observaciones'];


    public function user(){
        return $this->belongsTo(User::class);
    }

}
