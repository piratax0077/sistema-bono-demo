<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsumoFieldsToClienteSaldosTable extends Migration
{
    public function up()
    {
        Schema::table('cliente_saldos', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_consumido_id')->nullable()->after('voucher_id');
            $table->timestamp('consumido_en')->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('cliente_saldos', function (Blueprint $table) {
            $table->dropColumn([
                'voucher_consumido_id',
                'consumido_en',
            ]);
        });
    }
}

