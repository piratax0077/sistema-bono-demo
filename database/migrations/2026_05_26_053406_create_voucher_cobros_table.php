<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherCobrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_cobros', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('voucher_id');

        $table->unsignedBigInteger('veterinario_id')->nullable();

        $table->string('veterinario_nombre')->nullable();

        $table->string('sucursal')->nullable();

        $table->decimal('monto_cobrado', 10, 2)->default(0);

        $table->string('estado')->default('pendiente_rendicion');

        $table->timestamp('cobrado_en')->nullable();

        $table->timestamps();

        $table->foreign('voucher_id')
            ->references('id')
            ->on('vouchers')
            ->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('voucher_cobros');
}
}
