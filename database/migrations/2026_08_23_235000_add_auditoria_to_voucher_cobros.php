<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_cobros', function (Blueprint $table) {
            $table->unsignedBigInteger('auditor_id')->nullable()->after('profesional_id');
            $table->timestamp('auditado_at')->nullable()->after('cobrado_en');
            $table->text('observacion_auditor')->nullable()->after('auditado_at');
            $table->json('resultado_controles')->nullable()->after('observacion_auditor');
            $table->string('hash_visto_bueno', 64)->nullable()->after('resultado_controles');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_cobros', function (Blueprint $table) {
            $table->dropColumn([
                'auditor_id',
                'auditado_at',
                'observacion_auditor',
                'resultado_controles',
                'hash_visto_bueno',
            ]);
        });
    }
};
