<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendedorIdToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->unsignedBigInteger('vendedor_id')
            ->nullable()
            ->after('id');

    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->dropColumn('vendedor_id');

    });
}
}
