<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agenda_online_horarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profesional_id')->index();
            $table->unsignedBigInteger('servicio_id')->index();
            $table->dateTime('fecha_hora')->index();
            $table->unsignedSmallInteger('duracion_minutos')->default(30);
            $table->string('centro_nombre');
            $table->string('centro_email')->nullable();
            $table->string('centro_telefono', 50)->nullable();
            $table->string('centro_direccion')->nullable();
            $table->string('lugar_atencion')->nullable();
            $table->string('estado', 30)->default('disponible')->index();
            $table->unsignedBigInteger('reservado_por')->nullable()->index();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['profesional_id', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_online_horarios');
    }
};
