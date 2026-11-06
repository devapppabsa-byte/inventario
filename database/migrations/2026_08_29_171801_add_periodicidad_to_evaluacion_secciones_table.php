<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodicidadToEvaluacionSeccionesTable extends Migration
{
    public function up()
    {
        Schema::table('evaluacion_secciones', function (Blueprint $table) {
            $table->string('periodicidad')->default('mensual')->after('descripcion');
        });
    }

    public function down()
    {
        Schema::table('evaluacion_secciones', function (Blueprint $table) {
            $table->dropColumn('periodicidad');
        });
    }
}
