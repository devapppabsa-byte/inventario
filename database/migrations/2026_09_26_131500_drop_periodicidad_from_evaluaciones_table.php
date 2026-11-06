<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La periodicidad pertenece a las subencuetas, no a la encuesta principal.
 * Cada subencueta bloquea su propio reintento, asi que el valor de la
 * encuesta no significaba nada y solo confundia (era el maximo de sus
 * secciones, tomaba de una subencueta que el usuario podia no tener asignada).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropColumn('periodicidad');
        });
    }

    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->string('periodicidad')->default('mensual')->after('descripcion');
        });
    }
};
