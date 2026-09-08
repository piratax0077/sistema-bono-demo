<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_cobros', function (Blueprint $table) {
            $table->string('pago_estado')->nullable()->after('hash_visto_bueno');
            $table->unsignedBigInteger('pago_decidido_por')->nullable()->after('pago_estado');
            $table->timestamp('pago_decidido_at')->nullable()->after('pago_decidido_por');
            $table->text('pago_objecion')->nullable()->after('pago_decidido_at');
            $table->string('deposito_comprobante')->nullable()->after('pago_objecion');
        });

        DB::table('voucher_cobros')
            ->where('estado', 'pendiente_rendicion')
            ->whereNotNull('auditor_id')
            ->whereNotNull('hash_visto_bueno')
            ->update(['pago_estado' => 'pendiente_autorizacion']);
    }

    public function down(): void
    {
        Schema::table('voucher_cobros', function (Blueprint $table) {
            $table->dropColumn([
                'pago_estado',
                'pago_decidido_por',
                'pago_decidido_at',
                'pago_objecion',
                'deposito_comprobante',
            ]);
        });
    }
};
