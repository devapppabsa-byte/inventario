<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'colores', 'user_id', 'fecha_pedido', 'marca', 'cantidad', 'foto_tintas'];
    protected $table = 'pedidos';


    public function user(){
        return $this->belongsTo(User::class);
    }



}
