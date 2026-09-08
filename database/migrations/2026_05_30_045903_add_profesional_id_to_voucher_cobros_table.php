<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfesionalIdToVoucherCobrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('voucher_cobros', function (Blueprint $table) {
        $table->unsignedBigInteger('profesional_id')
            ->nullable()
            ->after('voucher_id');
    });
}

public function down()
{
    Schema::table('voucher_cobros', function (Blueprint $table) {
        $table->dropColumn('profesional_id');
    });
}
}
