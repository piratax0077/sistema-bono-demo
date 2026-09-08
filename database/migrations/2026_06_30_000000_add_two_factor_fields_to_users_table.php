<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            }

            if (! Schema::hasColumn('users', 'two_factor_last_verified_at')) {
                $table->timestamp('two_factor_last_verified_at')->nullable()->after('two_factor_confirmed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('users', 'two_factor_secret') ? 'two_factor_secret' : null,
                Schema::hasColumn('users', 'two_factor_confirmed_at') ? 'two_factor_confirmed_at' : null,
                Schema::hasColumn('users', 'two_factor_last_verified_at') ? 'two_factor_last_verified_at' : null,
            ]));

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
