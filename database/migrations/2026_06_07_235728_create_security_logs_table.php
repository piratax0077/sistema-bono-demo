<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('security_logs', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index();

            $table->string('accion')->index();

            $table->string('modelo')->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();

            $table->string('ip')->nullable();

            $table->string('device')->nullable();

            $table->string('estado')->default('ok');

            $table->text('detalle')->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('security_logs');
    }
}
