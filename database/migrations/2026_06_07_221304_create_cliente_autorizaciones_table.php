<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteAutorizacionesTable extends Migration
{
    public function up()
    {
        Schema::create('cliente_autorizaciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cliente_id')->index();
            $table->unsignedBigInteger('dispositivo_id')->nullable()->index();

            $table->string('tipo_accion')->index();
            $table->string('referencia_tipo')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();

            $table->string('token', 80)->unique();

            $table->enum('estado', [
                'pendiente',
                'aprobada',
                'rechazada',
                'expirada'
            ])->default('pendiente');

            $table->string('ip_solicitante')->nullable();
            $table->timestamp('expira_at')->nullable();
            $table->timestamp('aprobada_at')->nullable();
            $table->timestamp('rechazada_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_autorizaciones');
    }
}
