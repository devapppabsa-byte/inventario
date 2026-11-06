<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminAccesoSeeder extends Seeder
{
    /**
     * Crea (o actualiza) el administrador de acceso general.
     *
     * Es idempotente: se identifica por correo, asi que correrlo varias veces
     * no duplica registros. NO borra ni modifica ningun otro administrador,
     * usuario o dato existente.
     *
     * Uso: php artisan db:seed --class=AdminAccesoSeeder
     */
    public function run()
    {
        Admin::unguarded(function () {
            Admin::updateOrCreate(
                ['email' => 'admin@grupopabsa.com'],
                ['nombre' => 'Admin', 'password' => bcrypt('password')]
            );
        });
    }
}
