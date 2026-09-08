<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteIdToVoucherMascotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::table('voucher_mascotas', function (Blueprint $table) {
        if (!Schema::hasColumn('voucher_mascotas', 'cliente_id')) {
            $table->unsignedBigInteger('cliente_id')->nullable()->after('id');
            $table->index('cliente_id');
        }

        if (!Schema::hasColumn('voucher_mascotas', 'edad')) {
            $table->integer('edad')->nullable()->after('fecha_nacimiento');
        }
    });
}

public function down()
{
    Schema::table('voucher_mascotas', function (Blueprint $table) {
        if (Schema::hasColumn('voucher_mascotas', 'cliente_id')) {
            $table->dropIndex(['cliente_id']);
            $table->dropColumn('cliente_id');
        }

        if (Schema::hasColumn('voucher_mascotas', 'edad')) {
            $table->dropColumn('edad');
        }
    });
}


}
