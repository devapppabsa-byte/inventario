<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Computadora extends Model
{
    use HasFactory;

    protected $fillable = ['area', 'marca', 'modelo', 'pulgadas', 'touch', 'so', 'procesador', 'usuario', 'ip', 'mac', 'tipo', 'serie', 'size_hdd', 'size_ssd', 'imagen1', 'imagen2', 'imagen3', 'procesador', 'nuevo', 'ram', 'accesorios', 'estado', 'observaciones'];


    
    public function user(){
        return $this->belongsTo(User::class);
    }




}
