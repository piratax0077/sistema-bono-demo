<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpAutorizadasTable extends Migration
{
    public function up()
    {
        Schema::create('ip_autorizadas', function (Blueprint $table) {
            $table->id();

            $table->string('rol')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('ip');
            $table->string('descripcion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->index('rol');
            $table->index('user_id');
            $table->index('ip');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ip_autorizadas');
    }
}
