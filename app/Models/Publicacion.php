<?php

namespace App\Models;

use App\Models\Visita;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Publicacion extends Model
{
    use HasFactory;


    protected $table = 'publicaciones';
    protected $fillable= ['titulo', 'categoria', 'introduccion', 'portada', 'cuerpo', 'autor'];


    public function comentarios(){
        return  $this->hasMany(Comentario::class);
    }

    public function reacciones(){
        return $this->hasMany(Reaccion::class);
    }

    public function visitas(){
        return $this->hasMany(Visita::class);
    }







}
