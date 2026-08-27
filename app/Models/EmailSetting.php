<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    use HasFactory;

    protected $fillable = ['seccion', 'mail_from', 'mail_from_name', 'destinatario', 'cc'];

    public static function obtener($seccion)
    {
        return static::where('seccion', $seccion)->first();
    }
}
