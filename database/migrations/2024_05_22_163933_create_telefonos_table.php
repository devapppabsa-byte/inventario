<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelefonosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telefonos', function (Blueprint $table) {

            $table->id();
            $table->string('marca');
            $table->boolean('nuevo')->default(true);
            $table->string('modelo');
            $table->string('serie');
            $table->string('comparte')->nullable();
            $table->string('tipo');
            $table->string('estado');
            $table->string('imagen1')->nullable()->default();
            $table->string('imagen2')->nullable()->default();
            $table->string('imagen3')->nullable()->default();
            $table->string('observaciones');
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
        Schema::dropIfExists('telefonos');
    }
}
