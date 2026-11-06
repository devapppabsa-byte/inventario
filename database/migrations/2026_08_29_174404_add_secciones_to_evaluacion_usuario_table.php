<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeccionesToEvaluacionUsuarioTable extends Migration
{
    public function up()
    {
        Schema::table('evaluacion_usuario', function (Blueprint $table) {
            $table->json('secciones')->nullable()->after('activo');
        });
    }

    public function down()
    {
        Schema::table('evaluacion_usuario', function (Blueprint $table) {
            $table->dropColumn('secciones');
        });
    }
}
