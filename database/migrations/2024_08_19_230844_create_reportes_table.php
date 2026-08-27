<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->string('dispositivo');
            $table->string('otro')->nullable();
            $table->string('descripcion');
            $table->string('status')->nullable()->default('pendiente');
            $table->string('fecha_reporte');
            $table->string('prioridad');
            $table->string('fecha_solucion')->nullable()->default('pendiente');
            $table->string('detalles_solucion')->nullable()->default('pendiente');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reportes');
    }
}
