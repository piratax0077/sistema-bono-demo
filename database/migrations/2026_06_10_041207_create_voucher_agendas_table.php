<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherAgendasTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_agendas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('voucher_id')->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->unsignedBigInteger('mascota_id')->nullable()->index();

            $table->unsignedBigInteger('profesional_id')->nullable()->index();
            $table->unsignedBigInteger('centro_atencion_id')->nullable()->index();

            $table->dateTime('fecha_hora_solicitada')->nullable();
            $table->dateTime('fecha_hora_confirmada')->nullable();

            $table->enum('estado', [
                'hora_solicitada',
                'hora_confirmada',
                'paciente_cancela',
                'prestador_cancela',
                'atencion_realizada',
                'no_asiste'
            ])->default('hora_solicitada');

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_agendas');
    }
}
