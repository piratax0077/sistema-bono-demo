<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotemLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('totem_logs', function (Blueprint $table) {

        $table->id();

        $table->foreignId('totem_id')
            ->constrained('totems')
            ->onDelete('cascade');

        $table->string('evento');

        $table->text('detalle')
            ->nullable();

        $table->string('ip')
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
        Schema::dropIfExists('totem_logs');
    }
}
