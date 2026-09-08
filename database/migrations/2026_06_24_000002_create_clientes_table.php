<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('rut');
            $table->string('rut_hash', 64)->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('tipo', 50)->default('dueno_mascota');
            $table->string('estado', 50)->default('pendiente_pago');
            $table->timestamp('fecha_inscripcion')->nullable();
            $table->timestamps();

            $table->index(['estado', 'tipo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
