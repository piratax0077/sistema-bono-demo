<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_atenciones', function (Blueprint $table) {
            $table->text('diagnostico')->nullable()->after('riesgo');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_atenciones', function (Blueprint $table) {
            $table->dropColumn('diagnostico');
        });
    }
};
