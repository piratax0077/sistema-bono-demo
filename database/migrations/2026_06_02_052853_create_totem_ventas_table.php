<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotemVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('totem_ventas', function (Blueprint $table) {

        $table->id();

        $table->foreignId('totem_id')
            ->constrained('totems')
            ->onDelete('cascade');

        $table->foreignId('cliente_id')
            ->nullable();

        $table->decimal('total',12,2);

        $table->string('estado')
            ->default('pendiente');

        $table->string('medio_pago')
            ->nullable();

        $table->string('codigo_transaccion')
            ->nullable();

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
        Schema::dropIfExists('totem_ventas');
    }
}
