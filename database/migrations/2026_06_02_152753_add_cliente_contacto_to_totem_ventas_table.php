<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteContactoToTotemVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('totem_ventas', function (Blueprint $table) {
        $table->string('cliente_rut')->nullable()->after('cliente_id');
        $table->string('cliente_nombre')->nullable()->after('cliente_rut');
        $table->string('cliente_telefono')->nullable()->after('cliente_nombre');
        $table->string('cliente_email')->nullable()->after('cliente_telefono');
    });
}

public function down()
{
    Schema::table('totem_ventas', function (Blueprint $table) {
        $table->dropColumn([
            'cliente_rut',
            'cliente_nombre',
            'cliente_telefono',
            'cliente_email',
        ]);
    });
}
}
