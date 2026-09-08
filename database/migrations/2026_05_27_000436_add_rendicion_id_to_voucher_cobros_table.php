<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRendicionIdToVoucherCobrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('voucher_cobros', function (Blueprint $table) {

        $table->unsignedBigInteger('voucher_rendicion_id')
            ->nullable()
            ->after('voucher_id');

    });
}

public function down()
{
    Schema::table('voucher_cobros', function (Blueprint $table) {

        $table->dropColumn('voucher_rendicion_id');

    });
}
}
