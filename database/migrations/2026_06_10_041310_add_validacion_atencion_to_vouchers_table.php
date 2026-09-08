<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidacionAtencionToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('agenda_id')->nullable()->index();
            $table->unsignedBigInteger('atencion_id')->nullable()->index();

            $table->unsignedBigInteger('profesional_atendio_id')->nullable()->index();
            $table->unsignedBigInteger('asistente_valido_id')->nullable()->index();

            $table->dateTime('atencion_cerrada_at')->nullable();
            $table->dateTime('validado_at')->nullable();

            $table->string('ip_profesional')->nullable();
            $table->string('ip_asistente')->nullable();

            $table->string('estado_validacion')->nullable()->index();
            $table->string('riesgo_validacion')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'agenda_id',
                'atencion_id',
                'profesional_atendio_id',
                'asistente_valido_id',
                'atencion_cerrada_at',
                'validado_at',
                'ip_profesional',
                'ip_asistente',
                'estado_validacion',
                'riesgo_validacion',
            ]);
        });
    }
}
