<?php

namespace App\Models;

use App\Models\Publicacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reaccion extends Model
{
    use HasFactory;


    protected $table = 'reacciones';
    protected $fillable = ['reaccion', 'user_id', 'publicacion_id'];



    public function publicacion(){
        return $this->belongsTo(Publicacion::class);
    }


    public function user(){
        return $this->belongsTo(User::class);
    }



}
