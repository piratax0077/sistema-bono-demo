<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteRutHashToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('cliente_rut_hash', 64)
                ->nullable()
                ->after('cliente_rut');

            $table->index('cliente_rut_hash');
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['cliente_rut_hash']);
            $table->dropColumn('cliente_rut_hash');
        });
    }
}
