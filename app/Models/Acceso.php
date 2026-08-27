<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acceso extends Model
{
    use HasFactory;



    protected $fillable = ['nombre', 'tipo','status','autorizo', 'justificacion', 'user_id', 'id_jefe'];


    public function user(){

        return $this->belongsTo(User::class);
        
    }

}
