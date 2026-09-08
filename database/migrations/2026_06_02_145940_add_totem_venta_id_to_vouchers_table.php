<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotemVentaIdToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->unsignedBigInteger('totem_venta_id')
              ->nullable()
              ->after('id');

        $table->index('totem_venta_id');
    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {

        $table->dropIndex(['totem_venta_id']);
        $table->dropColumn('totem_venta_id');

    });
}
}
