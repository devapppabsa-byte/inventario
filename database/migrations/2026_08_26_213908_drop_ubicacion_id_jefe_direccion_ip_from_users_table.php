<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUbicacionIdJefeDireccionIpFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ubicacion', 'id_jefe', 'direccion_ip']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ubicacion')->nullable()->after('password');
            $table->string('id_jefe')->nullable()->after('jefe');
            $table->string('direccion_ip')->nullable()->default('0.0.0.0')->after('aspel');
        });
    }
}
