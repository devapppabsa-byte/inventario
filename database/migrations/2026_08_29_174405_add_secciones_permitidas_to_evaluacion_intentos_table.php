<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeccionesPermitidasToEvaluacionIntentosTable extends Migration
{
    public function up()
    {
        Schema::table('evaluacion_intentos', function (Blueprint $table) {
            $table->json('secciones_permitidas')->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('evaluacion_intentos', function (Blueprint $table) {
            $table->dropColumn('secciones_permitidas');
        });
    }
}
