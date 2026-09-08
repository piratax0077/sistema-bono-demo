<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherRendicionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_rendiciones', function (Blueprint $table) {
        $table->id();

        $table->string('veterinario_nombre')->nullable();
        $table->string('sucursal')->nullable();

        $table->decimal('total_cobrado', 10, 2)->default(0);
        $table->integer('cantidad_vouchers')->default(0);

        $table->string('estado')->default('pendiente');

        $table->timestamp('rendida_en')->nullable();
        $table->timestamp('pagada_en')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('voucher_rendiciones');
}
}
