<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HardenTotemSessions extends Migration
{
    public function up()
    {
        Schema::table('totems', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->unique()->after('token');
            $table->string('auth_secret_hash')->nullable()->after('token_hash');
            $table->timestamp('token_expira_at')->nullable()->after('auth_secret_hash');
        });

        Schema::table('totem_sesiones', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->index()->after('token');
            $table->timestamp('expira_at')->nullable()->after('fin');
            $table->string('ip', 45)->nullable()->after('expira_at');
        });

        // Los tokens históricos del respaldo estaban almacenados en texto plano.
        // Se invalidan al actualizar; los datos comerciales y de auditoría se conservan.
        DB::table('totem_sesiones')->update([
            'token' => '',
            'fin' => DB::raw('COALESCE(fin, CURRENT_TIMESTAMP)'),
        ]);

        DB::table('totems')->update([
            'token' => null,
            'token_hash' => null,
            'token_expira_at' => null,
        ]);
    }

    public function down()
    {
        Schema::table('totem_sesiones', function (Blueprint $table) {
            $table->dropIndex(['token_hash']);
            $table->dropColumn(['token_hash', 'expira_at', 'ip']);
        });

        Schema::table('totems', function (Blueprint $table) {
            $table->dropUnique(['token_hash']);
            $table->dropColumn(['token_hash', 'auth_secret_hash', 'token_expira_at']);
        });
    }
}
