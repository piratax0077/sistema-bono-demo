<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherPreconsultaAuditoriasTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_preconsulta_auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preconsulta_id')
                ->constrained('voucher_preconsultas')
                ->cascadeOnDelete();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->text('motivo')->nullable();
            $table->json('contradicciones')->nullable();
            $table->unsignedInteger('intentos_fallidos_count')->default(0);
            $table->json('intentos_fallidos')->nullable();
            $table->json('porques')->nullable();
            $table->json('contexto')->nullable();
            $table->foreignId('auditor_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('resolucion')->nullable();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();

            $table->unique('preconsulta_id');
            $table->index(['estado', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_preconsulta_auditorias');
    }
}
