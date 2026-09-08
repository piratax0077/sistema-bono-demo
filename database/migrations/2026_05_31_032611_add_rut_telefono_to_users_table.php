<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRutTelefonoToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::table('users', function (Blueprint $table) {

        $table->string('rut')
            ->nullable()
            ->after('email');

        $table->string('telefono')
            ->nullable()
            ->after('rut');

    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn('rut');
        $table->dropColumn('telefono');

    });
}

}
