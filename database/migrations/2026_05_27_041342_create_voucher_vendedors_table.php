<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherVendedorsTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_vendedores', function (Blueprint $table) {

            $table->id();

            $table->string('nombre');

            $table->string('rut')->nullable();

            $table->string('email')->nullable();

            $table->string('telefono')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_vendedores');
    }
}
