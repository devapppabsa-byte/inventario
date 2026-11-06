<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionOpcionesTable extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_opciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pregunta_id');
            $table->string('texto');
            $table->decimal('puntos', 8, 2)->default(0);
            $table->decimal('valor', 8, 2)->default(0);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('pregunta_id')->references('id')->on('evaluacion_preguntas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_opciones');
    }
}
