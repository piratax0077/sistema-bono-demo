<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('vouchers', function (Blueprint $table) {
        $table->id();

        $table->string('codigo')->unique();
        $table->string('qr_token')->unique();

        $table->unsignedBigInteger('cliente_id')->nullable();
        $table->unsignedBigInteger('mascota_id')->nullable();
        $table->unsignedBigInteger('criadero_cachorro_id')->nullable();

        $table->string('cliente_rut')->nullable();
        $table->string('cliente_nombre')->nullable();

        $table->string('tipo_servicio')->nullable();
        $table->decimal('valor', 10, 2)->default(0);
        $table->decimal('porcentaje_descuento', 5, 2)->default(100);

        $table->string('estado')->default('activo');

        $table->timestamp('fecha_vencimiento')->nullable();
        $table->timestamp('usado_en')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('vouchers');
}
}
