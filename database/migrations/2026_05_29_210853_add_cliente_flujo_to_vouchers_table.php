<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteFlujoToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->timestamp('cliente_aceptado_en')->nullable();
        $table->timestamp('cliente_rechazado_en')->nullable();
        $table->text('motivo_rechazo_cliente')->nullable();
    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->dropColumn([
            'cliente_aceptado_en',
            'cliente_rechazado_en',
            'motivo_rechazo_cliente',
        ]);
    });
}

}
