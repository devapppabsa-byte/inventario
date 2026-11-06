<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionRespuestasTable extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_respuestas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intento_id');
            $table->unsignedBigInteger('pregunta_id');
            $table->unsignedBigInteger('opcion_id')->nullable();
            $table->decimal('puntos_obtenidos', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('intento_id')->references('id')->on('evaluacion_intentos')->onDelete('cascade');
            $table->foreign('pregunta_id')->references('id')->on('evaluacion_preguntas')->onDelete('cascade');
            $table->foreign('opcion_id')->references('id')->on('evaluacion_opciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_respuestas');
    }
}
