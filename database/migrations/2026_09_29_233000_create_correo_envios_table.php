<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorreoEnviosTable extends Migration
{
    public function up()
    {
        // Un solo registro por envio, para los dos flujos. Antes el unico rastro
        // era una linea en storage/logs, y se perdia en el rotation: no habia
        // forma de saber que correos se quedaron sin enviar ni reintentarlos.
        Schema::create('correo_envios', function (Blueprint $table) {
            $table->id();
            $table->string('seccion')->index();          // soporte | tintas
            $table->string('enviable_type');              // App\Models\Reporte | App\Models\Pedido
            $table->unsignedBigInteger('enviable_id');
            $table->string('estado')->index();            // enviado | fallido
            $table->text('destinatarios')->nullable();
            $table->unsignedTinyInteger('intentos')->default(1);
            $table->text('error')->nullable();
            $table->timestamp('intento_en')->nullable();
            $table->timestamp('entregado_en')->nullable();
            $table->timestamps();

            $table->index(['enviable_type', 'enviable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('correo_envios');
    }
}
