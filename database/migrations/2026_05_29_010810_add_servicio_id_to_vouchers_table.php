<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicioIdToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->unsignedBigInteger('servicio_id')
            ->nullable()
            ->after('profesional_id');
    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->dropColumn('servicio_id');
    });
}
}
