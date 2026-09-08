<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUltimoPingToTotemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('totems', function (Blueprint $table) {
        $table->timestamp('ultimo_ping')->nullable()->after('ultimo_acceso');
    });
}

public function down()
{
    Schema::table('totems', function (Blueprint $table) {
        $table->dropColumn('ultimo_ping');
    });
}
}
