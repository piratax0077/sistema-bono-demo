<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_servicios', function (Blueprint $table) {

        $table->id();

        $table->string('nombre');

        $table->text('descripcion')->nullable();

        $table->decimal('valor_base', 10, 2)
            ->default(0);

        $table->decimal('copago_base', 10, 2)
            ->default(0);

        $table->decimal('comision_veterchile', 10, 2)
            ->default(0);

        $table->boolean('activo')
            ->default(true);

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('voucher_servicios');
}
}
