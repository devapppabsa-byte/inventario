<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenteMensaje extends Model
{
    use HasFactory;

    protected $table = 'asistente_mensajes';

    protected $fillable = ['user_id', 'role', 'contenido'];

    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
