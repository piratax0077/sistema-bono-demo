<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE voucher_atenciones MODIFY estado ENUM(
            'abierta',
            'cerrada_por_profesional',
            'validada_por_asistente',
            'validada_automaticamente',
            'observada',
            'rechazada'
        ) NOT NULL DEFAULT 'abierta'");
    }

    public function down(): void
    {
        DB::table('voucher_atenciones')
            ->where('estado', 'validada_automaticamente')
            ->update(['estado' => 'cerrada_por_profesional']);

        DB::statement("ALTER TABLE voucher_atenciones MODIFY estado ENUM(
            'abierta',
            'cerrada_por_profesional',
            'validada_por_asistente',
            'observada',
            'rechazada'
        ) NOT NULL DEFAULT 'abierta'");
    }
};
