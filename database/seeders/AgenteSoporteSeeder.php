<?php

namespace Database\Seeders;

use App\Models\AgenteSoporte;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgenteSoporteSeeder extends Seeder
{
    /**
     * Crea o actualiza el agente inicial de soporte.
     *
     * Corre con:  php artisan db:seed --class=AgenteSoporteSeeder
     *
     * La clave se toma de SOPORTE_AGENTE_PASSWORD en .env; si no esta, se
     * genera una aleatoria y se muestra en consola para poder copiarla.
     */
    public function run()
    {
        $nombre = env('SOPORTE_AGENTE_NOMBRE', 'Agente de Soporte');
        $email = env('SOPORTE_AGENTE_EMAIL', 'soporte@inventario.local');

        if (env('SOPORTE_AGENTE_PASSWORD')) {
            $password = env('SOPORTE_AGENTE_PASSWORD');
        } else {
            $password = 'Soporte' . random_int(1000, 9999);
            $this->command->warn('No se encontro SOPORTE_AGENTE_PASSWORD en .env.');
            $this->command->info('Clave generada para ' . $email . ': ' . $password);
        }

        // Solo crea. Si el correo ya existe no lo toca: ahora los agentes se
        // dan de alta desde el panel y un updateOrCreate al re-ejecutar esta
        // Seed les reiniciaria la clave y los reactivaria a la fuerza.
        $existente = AgenteSoporte::where('email', $email)->first();

        if ($existente) {
            $this->command->warn('El agente ' . $email . ' ya existe; no se modifico nada.');
            $this->command->info('Para cambiar su clave o estado usa el panel: /admin/soporte_agentes');
            return;
        }

        AgenteSoporte::create([
            'nombre' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'activo' => true,
        ]);

        $this->command->info('Agente de soporte creado: ' . $email);
    }
}
