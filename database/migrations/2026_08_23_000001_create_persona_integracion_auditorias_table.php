<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persona_integracion_auditorias', function (Blueprint $table) {
            $table->id();
            $table->string('operacion', 40)->index();
            $table->string('rut_hash', 64)->nullable()->index();
            $table->string('resultado', 30)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duracion_ms')->nullable();
            $table->uuid('correlation_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('totem_id')->nullable()->constrained('totems')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('detalle', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_integracion_auditorias');
    }
};
