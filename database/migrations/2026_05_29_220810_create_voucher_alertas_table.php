<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherAlertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('voucher_alertas', function (Blueprint $table) {

        $table->id();

        $table->foreignId('voucher_id');

        $table->string('tipo_alerta');

        $table->enum('nivel', [
            'verde',
            'amarillo',
            'rojo'
        ]);

        $table->text('descripcion');

        $table->boolean('resuelta')
            ->default(false);

        $table->timestamps();
    });
}
}
