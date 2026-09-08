<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinanzasToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->decimal('copago_usuario', 10, 2)->default(0);
        $table->decimal('saldo_veterinario', 10, 2)->default(0);
        $table->decimal('comision_veterchile', 10, 2)->default(0);
    });
}

public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->dropColumn([
            'copago_usuario',
            'saldo_veterinario',
            'comision_veterchile',
        ]);
    });
}

}
