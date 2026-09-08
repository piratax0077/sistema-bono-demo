<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfesionalIdToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->unsignedBigInteger('profesional_id')
            ->nullable()
            ->after('vendedor_id');

    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->dropColumn('profesional_id');

    });
}
}
