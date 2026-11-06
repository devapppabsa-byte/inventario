<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CreateReporteAdjuntosTable extends Migration
{
    public function up()
    {
        Schema::create('reporte_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporte_id');
            $table->string('ruta', 255);
            $table->string('nombre_original', 255);
            $table->string('mime', 120)->nullable();
            $table->unsignedInteger('tamano')->default(0);
            $table->boolean('es_imagen')->default(false);
            $table->timestamps();

            $table->index('reporte_id');
            $table->foreign('reporte_id')->references('id')->on('reportes')->onDelete('cascade');
        });

        $this->migrarImagenesAntiguas();
    }

    public function down()
    {
        Schema::dropIfExists('reporte_adjuntos');
    }

    /**
     * Los reportes anteriores guardaban la evidencia en la columna `img`. Se copian
     * a la tabla nueva para que usuario, agente y PDF los muestren todos desde el
     * mismo lugar. La columna `img` no se toca: sigue llenándose y sirve de red de
     * seguridad si algo hay que revertir.
     */
    private function migrarImagenesAntiguas()
    {
        $filas = DB::table('reportes')
            ->select('id', 'img')
            ->whereNotNull('img')
            ->where('img', '!=', '')
            ->orderBy('id')
            ->get();

        foreach ($filas as $fila) {
            $ruta = (string) $fila->img;

            // Si el archivo ya no está en disco no hay nada que servir.
            if (!Storage::disk('local')->exists($ruta)) {
                continue;
            }

            try {
                $mime = Storage::disk('local')->mimeType($ruta);
            } catch (\Throwable $e) {
                $mime = null;
            }

            DB::table('reporte_adjuntos')->insert([
                'reporte_id'       => $fila->id,
                'ruta'             => $ruta,
                'nombre_original'  => basename($ruta),
                'mime'             => $mime,
                'tamano'           => (int) Storage::disk('local')->size($ruta),
                'es_imagen'        => $mime ? str_starts_with($mime, 'image/') : false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
