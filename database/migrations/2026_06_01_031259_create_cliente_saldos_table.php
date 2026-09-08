<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteSaldosTable extends Migration
{
    public function up()
    {
        Schema::create('cliente_saldos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('voucher_id')->nullable();

            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_rut_hash', 64)->nullable();

            $table->integer('monto')->default(0);

            $table->string('origen')->default('voucher_invalidado');
            $table->string('estado')->default('disponible');

            $table->text('descripcion')->nullable();

            $table->timestamps();

            $table->index('cliente_rut_hash');
            $table->index('voucher_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_saldos');
    }
}
