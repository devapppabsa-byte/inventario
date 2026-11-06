<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Acceso;
use App\Models\Pedido;
use App\Models\Reporte;
use App\Models\Telefono;
use App\Models\Impresora;
use App\Models\Computadora;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(EmailSettingSeeder::class);



        $usuario = new User();
        $usuario->name = "Arturo Resendiz";
        $usuario->email = "resendiz.galleta@gmail.com";
        $usuario->puesto = "Encargado de sistemas";
        $usuario->planta = "Planta 1";
        $usuario->extension = "212";
        $usuario->jefe = 1;
        $usuario->id_jefe = "Gerencia";
        $usuario->celular = "No hay celular";
        $usuario->password = bcrypt('password');
        $usuario->ubicacion = "Edificio administrativo tercer piso junto a servicarga";
        $usuario->save();








        // $this->call(AdminSeeder::class);

        $users = User::all();

        foreach($users as $user){


            Reporte::factory(30)->create([
                'user_id' =>$user->id
            ]);






            Computadora::factory(2)->create([
                'user_id' => $user->id
            ]);


            Pedido::factory(3)->create([
                'user_id' => $user->id
            ]);

            
            Impresora::factory(1)->create([
                'user_id' => $user->id,
                'comparte' => $user->name
            ]);

            Telefono::factory(1)->create([
                'user_id' => $user->id
            ]);

            Acceso::factory(2)->create([
                'user_id' => $user->id,
                'id_jefe' => $user->id
            ]);

        }




        $this->call(EncuestaProveedorTISeeder::class);

    }
}
