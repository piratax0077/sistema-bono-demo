<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotemVentaDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('totem_venta_detalles', function (Blueprint $table) {

        $table->id();

        $table->foreignId('venta_id')
            ->constrained('totem_ventas')
            ->onDelete('cascade');

        $table->string('tipo');

        $table->unsignedBigInteger('referencia_id');

        $table->integer('cantidad')
            ->default(1);

        $table->decimal('precio',12,2);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('totem_venta_detalles');
    }
}
