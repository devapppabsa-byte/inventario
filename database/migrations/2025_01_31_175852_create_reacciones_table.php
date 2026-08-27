<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReaccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reacciones', function (Blueprint $table) {
            $table->id();
            $table->string('reaccion');
            $table->unsignedBigInteger('publicacion_id'); //esta es la llave foranea a la tabla de publicaciones
            $table->unsignedBigInteger('user_id');//esta es la llave foranea a la tabla de los usuarios
            $table->timestamps();
                
            //definiendo la relacion
            $table->foreign('publicacion_id')
                  ->references('id')
                  ->on('publicaciones');

            //definicion de la relacion con la tabla usuarios
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');

            //evitar reacciones duplicadas
            $table->unique(['publicacion_id', 'user_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reacciones');
    }
}
