<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new Admin();

        $admin->nombre = "Arturo Resendiz";
        $admin->password = bcrypt('password');
        $admin->email = "arturo.resendiz@grupopabsa.com";
        
        $admin->save();
    }
}
