<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoporteAgentesTable extends Migration
{
    public function up()
    {
        // Tabla aparte de admin y de users a proposito: el agente entra por el
        // guard 'soporte', asi que las rutas auth:admin le quedan cerradas
        // sin depender de ninguna columna de rol.
        Schema::create('soporte_agentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('soporte_agentes');
    }
}
