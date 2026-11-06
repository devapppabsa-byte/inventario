<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionSeccionesTable extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_secciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluacion_id');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('evaluacion_id')->references('id')->on('evaluaciones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_secciones');
    }
}
