<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsistenteMensajesTable extends Migration
{
    public function up()
    {
        // Historial del asistente. El contexto de datos de cada usuario se
        // reconstruye al vuelo desde pedidos, reportes y evaluaciones; aqui solo
        // se guarda la conversacion para que sobreviva a recargas.
        Schema::create('asistente_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role');          // user | assistant
            $table->text('contenido');
            $table->timestamps();

            $table->index(['user_id', 'id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asistente_mensajes');
    }
}
