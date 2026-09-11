<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherCobroProgramacionesTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_cobro_programaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('frecuencia', 20)->default('semanal');
            $table->boolean('activo')->default(false);
            $table->timestamp('proxima_ejecucion_at')->nullable();
            $table->timestamp('ultima_ejecucion_at')->nullable();
            $table->unsignedInteger('ultimo_total_procesado')->default(0);
            $table->string('ultimo_resultado')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_cobro_programaciones');
    }
}
