<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherLiquidacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_liquidaciones', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('voucher_rendicion_id')->nullable();
        $table->unsignedBigInteger('profesional_id')->nullable();

        $table->string('profesional_nombre')->nullable();
        $table->string('banco')->nullable();
        $table->string('tipo_cuenta')->nullable();
        $table->string('numero_cuenta')->nullable();

        $table->decimal('monto_profesional', 10, 2)->default(0);
        $table->decimal('comision_veterchile', 10, 2)->default(0);

        $table->string('estado')->default('pendiente_pago');
        $table->string('medio_pago')->nullable();
        $table->string('comprobante_transferencia')->nullable();

        $table->timestamp('pagado_en')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('voucher_liquidaciones');
}
}
