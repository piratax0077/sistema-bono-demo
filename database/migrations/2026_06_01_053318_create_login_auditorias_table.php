<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginAuditoriasTable extends Migration
{
    public function up()
    {
        Schema::create('login_auditorias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('email')->nullable();
            $table->string('resultado')->default('fallido');

            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('email');
            $table->index('resultado');
            $table->index('ip');
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_auditorias');
    }
}
