<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToTotemVentaIdOnVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // El índice ya se crea junto con la columna en la migración 145940.
    }

    public function down()
    {
        // No-op: la migración 145940 administra el índice.
    }
}
