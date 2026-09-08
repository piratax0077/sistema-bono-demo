<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('totems', function (Blueprint $table) {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('nombre');
        $table->string('ubicacion')->nullable();
        $table->string('ip_autorizada')->nullable();
        $table->string('token')->nullable();
        $table->boolean('activo')->default(true);
        $table->string('version')->nullable();
        $table->string('serial')->nullable();
        $table->timestamp('ultimo_acceso')->nullable();
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
    Schema::dropIfExists('totems');
}
}
