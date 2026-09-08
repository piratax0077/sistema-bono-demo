<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateVoucherProfesionalsTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_profesionales', function (Blueprint $table) {

            $table->id();

            $table->string('nombre');

            $table->string('rut')->nullable();

            $table->string('especialidad')->nullable();

            $table->string('telefono')->nullable();

            $table->string('email')->nullable();

            $table->boolean('activo')->default(true);

            $table->string('banco')->nullable();

            $table->string('tipo_cuenta')->nullable();

            $table->string('numero_cuenta')->nullable();

            $table->string('titular_cuenta')->nullable();

            $table->string('rut_cuenta')->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_profesionales');
    }
}

