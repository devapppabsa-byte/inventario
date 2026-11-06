<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterReportesDescripcionToText extends Migration
{
    /**
     * La descripción pasa a texto enriquecido (HTML de Quill), que se pasa de los
     * 255 caracteres del string original con facilidad.
     *
     * No se usa ->change() porque doctrine/dbal no está en el proyecto y Laravel 8
     * lo necesita para modificar columnas. Para MySQL alcanza con un ALTER, que es
     * además más predecible; el resto de motores cae a la vía declarativa.
     */
    public function up()
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE reportes MODIFY descripcion TEXT NOT NULL');
            return;
        }

        Schema::table('reportes', function ($table) {
            $table->text('descripcion')->change();
        });
    }

    public function down()
    {
        // Volver a 255 truncaría datos ya guardados: se deja la columna como TEXT.
    }
}
