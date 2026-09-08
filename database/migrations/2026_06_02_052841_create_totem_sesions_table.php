<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotemSesionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('totem_sesiones', function (Blueprint $table) {

        $table->id();

        $table->foreignId('totem_id')
            ->constrained('totems')
            ->onDelete('cascade');

        $table->string('token',100);

        $table->timestamp('inicio');

        $table->timestamp('fin')->nullable();

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
    Schema::dropIfExists('totem_sesiones');
}
}
