<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'comentarios';
    protected $fillable = ['comentario', 'id_users', 'id_publicacion'];


    public function publicacion(){
        return $this->belongsTo(Publicacion::class);
    }

    public function user(){
        return $this->belongsTo(Publicacion::class, 'id_users', 'id');
    }


}
