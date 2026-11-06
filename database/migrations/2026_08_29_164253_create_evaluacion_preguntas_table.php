<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionPreguntasTable extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_preguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seccion_id');
            $table->string('pregunta');
            $table->decimal('puntos_maximos', 8, 2)->default(0);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('seccion_id')->references('id')->on('evaluacion_secciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_preguntas');
    }
}
