<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRolesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::table('users', function (Blueprint $table) {

        $table->string('rol')
            ->default('vendedor')
            ->after('email');

        $table->boolean('activo')
            ->default(true)
            ->after('rol');

        $table->unsignedBigInteger('vendedor_id')
            ->nullable()
            ->after('activo');

        $table->unsignedBigInteger('profesional_id')
            ->nullable()
            ->after('vendedor_id');

    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn([
            'rol',
            'activo',
            'vendedor_id',
            'profesional_id'
        ]);

    });
}
}
