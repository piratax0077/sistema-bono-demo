<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteDispositivosTable extends Migration
{
    public function up()
    {
        Schema::create('cliente_dispositivos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cliente_id')->index();

            $table->string('imei_hash', 64)->unique();
            $table->string('device_token')->nullable();
            $table->string('nombre_dispositivo')->nullable();

            $table->enum('estado', [
                'activo',
                'bloqueado',
                'pendiente'
            ])->default('pendiente');

            $table->timestamp('ultimo_uso_at')->nullable();
            $table->string('ip_registro')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_dispositivos');
    }
}
