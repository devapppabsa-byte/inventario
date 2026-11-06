<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionIntentosTable extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_intentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluacion_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->decimal('puntuacion', 8, 2)->default(0);
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->string('estado')->default('en_progreso');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->foreign('evaluacion_id')->references('id')->on('evaluaciones')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_intentos');
    }
}
