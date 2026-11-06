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

    /**
     * Arma la lista de copia del correo: lo configurado en la seccion mas el
     * usuario que creo el registro, para que quien reporta se quede con su copia
     * y no dependa de que soporte le escriba.
     *
     * Se usa users.correo y no users.email a proposito: email es la credencial
     * con la que se entra a la plataforma y correo es el de contacto. Mandarle
     * la copia al login seria mandarla a la direccion equivocada.
     *
     * La lista viene sucia desde el panel (comas, espacios, mayusculas
     * distintas), asi que se normaliza y se filtra antes de mandarse: una
     * direccion repetida o mal escrita hace que el envio entero falle. Un
     * usuario sin correo de contacto simplemente no entra en copia.
     *
     * @param  \App\Models\EmailSetting|null  $setting
     * @param  \App\Models\User|null  $usuario
     * @return array<int,string>
     */
    public static function copias($setting, $usuario = null)
    {
        $correos = [];

        if ($setting && !empty($setting->cc)) {
            $correos = array_map('trim', explode(',', $setting->cc));
        }

        if ($usuario && !empty($usuario->correo)) {
            $correos[] = trim($usuario->correo);
        }

        // El destinatario principal ya recibe el correo: si tambien esta en copia
        // la misma direccion aparece en To y en Cc, que some clientes rechazan.
        $principal = $setting && !empty($setting->destinatario)
            ? mb_strtolower(trim($setting->destinatario))
            : null;

        $correos = array_filter($correos, function ($correo) use ($principal) {
            if ($correo === '' || filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
                return false;
            }

            return $principal === null || mb_strtolower($correo) !== $principal;
        });

        // array_unique conserva las claves, hay que reindexar para que el cc
        // no viaje con huecos.
        return array_values(array_unique(array_map('mb_strtolower', $correos)));
    }
}
