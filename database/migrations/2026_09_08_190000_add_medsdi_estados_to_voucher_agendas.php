<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 'hora_reservada' y 'hora_rechazada' ya se usaban en el código
        // (AgendaExternaCompraService y la sincronización con Med-SDI) pero
        // faltaban en el enum real de la tabla.
        DB::statement("ALTER TABLE voucher_agendas MODIFY estado ENUM(
            'hora_solicitada',
            'hora_reservada',
            'hora_confirmada',
            'hora_rechazada',
            'paciente_en_espera',
            'paciente_cancela',
            'prestador_cancela',
            'atencion_realizada',
            'no_asiste'
        ) NOT NULL DEFAULT 'hora_solicitada'");
    }

    public function down(): void
    {
        DB::table('voucher_agendas')
            ->whereIn('estado', ['hora_reservada', 'hora_rechazada'])
            ->update(['estado' => 'hora_solicitada']);

        DB::statement("ALTER TABLE voucher_agendas MODIFY estado ENUM(
            'hora_solicitada',
            'hora_confirmada',
            'paciente_en_espera',
            'paciente_cancela',
            'prestador_cancela',
            'atencion_realizada',
            'no_asiste'
        ) NOT NULL DEFAULT 'hora_solicitada'");
    }
};
