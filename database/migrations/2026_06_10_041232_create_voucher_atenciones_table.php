<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherAtencionesTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_atenciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('voucher_id')->index();
            $table->unsignedBigInteger('agenda_id')->nullable()->index();

            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->unsignedBigInteger('mascota_id')->nullable()->index();

            $table->unsignedBigInteger('profesional_id')->nullable()->index();
            $table->unsignedBigInteger('asistente_id')->nullable()->index();

            $table->dateTime('inicio_atencion')->nullable();
            $table->dateTime('fin_atencion')->nullable();

            $table->dateTime('cerrada_at')->nullable();
            $table->dateTime('validada_at')->nullable();

            $table->string('ip_profesional')->nullable();
            $table->string('ip_asistente')->nullable();

            $table->text('user_agent_profesional')->nullable();
            $table->text('user_agent_asistente')->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('direccion')->nullable();

            $table->enum('estado', [
                'abierta',
                'cerrada_por_profesional',
                'validada_por_asistente',
                'observada',
                'rechazada'
            ])->default('abierta');

            $table->string('riesgo')->default('bajo');

            $table->string('hash_auditoria', 64)->nullable();

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_atenciones');
    }
}
