<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEncryptedFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->text('rut_encrypted')->nullable()->after('rut');
        $table->text('telefono_encrypted')->nullable()->after('telefono');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'rut_encrypted',
            'telefono_encrypted',
        ]);
    });
}
}
