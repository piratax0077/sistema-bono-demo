<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntregaToTotemVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('totem_ventas', function (Blueprint $table) {
    $table->string('canal_entrega')->nullable()->after('codigo_transaccion');
    $table->string('destino_entrega')->nullable()->after('canal_entrega');
    $table->timestamp('entregado_en')->nullable()->after('destino_entrega');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('totem_ventas', function (Blueprint $table) {
            //
        });
    }
}
