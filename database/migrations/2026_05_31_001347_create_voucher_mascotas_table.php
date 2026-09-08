<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherMascotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_mascotas', function (Blueprint $table) {
        $table->id();

        $table->string('nombre');
        $table->string('especie')->nullable();
        $table->string('raza')->nullable();
        $table->string('sexo')->nullable();
        $table->date('fecha_nacimiento')->nullable();
        $table->string('color')->nullable();
        $table->string('microchip')->nullable();

        $table->string('dueno_rut');
        $table->string('dueno_nombre')->nullable();
        $table->string('dueno_telefono')->nullable();
        $table->string('dueno_email')->nullable();

        $table->boolean('activo')->default(true);

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('voucher_mascotas');
}
}
