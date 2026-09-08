<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('vouchers', 'cliente_telefono')) {
                $table->string('cliente_telefono', 50)->nullable()->after('cliente_nombre');
            }

            if (! Schema::hasColumn('vouchers', 'cliente_email')) {
                $table->string('cliente_email', 150)->nullable()->after('cliente_telefono');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'cliente_email')) {
                $table->dropColumn('cliente_email');
            }

            if (Schema::hasColumn('vouchers', 'cliente_telefono')) {
                $table->dropColumn('cliente_telefono');
            }
        });
    }
};
