<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publicacion_id'); //esta es la llave foranea a la tabla de publicaciones
            $table->unsignedBigInteger('user_id');//esta es la llave foranea a la tabla de los usuarios
            $table->timestamps();

            //se definen las relaciones
            $table->foreign('publicacion_id')->references('id')->on('publicaciones');
            $table->foreign('user_id')->references('id')->on('users');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visitas');
    }
}
