<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('voucher_logs', function (Blueprint $table) {

    $table->id();

    $table->unsignedBigInteger('voucher_id');

    $table->unsignedBigInteger('user_id')->nullable();

    $table->string('accion');

    $table->string('ip')->nullable();

    $table->text('detalle')->nullable();

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
        Schema::dropIfExists('voucher_logs');
    }
}
