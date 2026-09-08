<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrestadorToTotemVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('totem_ventas', function (Blueprint $table) {
        $table->string('prestador_tipo')->nullable()->after('cliente_email');
        $table->unsignedBigInteger('prestador_id')->nullable()->after('prestador_tipo');
        $table->string('prestador_nombre')->nullable()->after('prestador_id');
    });
}

public function down()
{
    Schema::table('totem_ventas', function (Blueprint $table) {
        $table->dropColumn([
            'prestador_tipo',
            'prestador_id',
            'prestador_nombre',
        ]);
    });
}
}
