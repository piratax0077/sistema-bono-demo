<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoAutorizacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pago_autorizaciones', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('voucher_liquidacion_id')->nullable();

            $table->unsignedBigInteger('voucher_rendicion_id')->nullable();

            $table->unsignedBigInteger('profesional_id')->nullable();

            $table->unsignedBigInteger('admin_id')->nullable();

            $table->string('token', 120);

            $table->string('estado')->default('pendiente');

            $table->string('ip_solicitud')->nullable();

            $table->string('ip_respuesta')->nullable();

            $table->string('device_id')->nullable();

            $table->timestamp('expira_at')->nullable();

            $table->timestamp('aprobada_at')->nullable();

            $table->timestamp('rechazada_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pago_autorizacions');
    }
}
