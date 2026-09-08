<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_pagos', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('voucher_id');

        $table->decimal('monto_pagado_usuario', 10, 2)->default(0);
        $table->string('metodo_pago')->nullable();
        $table->string('estado_pago')->default('pendiente');
        $table->string('comprobante')->nullable();

        $table->timestamps();

        $table->foreign('voucher_id')
            ->references('id')
            ->on('vouchers')
            ->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('voucher_pagos');
}
}
