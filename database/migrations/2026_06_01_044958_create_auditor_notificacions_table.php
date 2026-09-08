<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditorNotificacionsTable extends Migration
{
    public function up()
    {
        Schema::create('auditor_notificaciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->unsignedBigInteger('alerta_id')->nullable();

            $table->string('titulo');
            $table->text('mensaje');

            $table->boolean('leido')->default(false);
            $table->timestamp('fecha_lectura')->nullable();

            $table->timestamps();

            $table->index('voucher_id');
            $table->index('alerta_id');
            $table->index('leido');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditor_notificaciones');
    }
}
