<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailSetting;

class EmailSettingSeeder extends Seeder
{
    public function run()
    {
        EmailSetting::updateOrCreate(
            ['seccion' => 'tintas'],
            [
                'mail_from' => 'sistemas@grupopabsa.com',
                'mail_from_name' => 'Sistema de Inventario',
                'destinatario' => 'sistemas@grupopabsa.com',
                'cc' => null,
            ]
        );

        EmailSetting::updateOrCreate(
            ['seccion' => 'soporte'],
            [
                'mail_from' => 'sistemas@grupopabsa.com',
                'mail_from_name' => 'Sistema de Inventario',
                'destinatario' => 'sistemas@grupopabsa.com',
                'cc' => null,
            ]
        );
    }
}
