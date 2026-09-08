<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherAuditoriasTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_auditorias', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('voucher_id')
                ->nullable();

            $table->string('accion');

            $table->string('usuario_tipo')
                ->nullable();

            $table->unsignedBigInteger('usuario_id')
                ->nullable();

            $table->text('descripcion')
                ->nullable();

            $table->string('ip')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_auditorias');
    }
}
