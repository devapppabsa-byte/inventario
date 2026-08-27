<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->json('colores');
            $table->string('marca')->nullable();
            $table->string('cantidad')->nullable();
            $table->string('respuesta_admin')->nullable();
            $table->string('status')->nullable()->default('pendiente');
            $table->string('fecha_pedido');
            $table->string('fecha_entrega')->nullable()->default('pendiente');
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
        Schema::dropIfExists('pedidos');
    }
}
