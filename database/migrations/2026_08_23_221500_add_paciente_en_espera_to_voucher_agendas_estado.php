<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        DB::table('voucher_agendas')
            ->where('estado', 'paciente_en_espera')
            ->update(['estado' => 'hora_confirmada']);

        DB::statement("ALTER TABLE voucher_agendas MODIFY estado ENUM(
            'hora_solicitada',
            'hora_confirmada',
            'paciente_cancela',
            'prestador_cancela',
            'atencion_realizada',
            'no_asiste'
        ) NOT NULL DEFAULT 'hora_solicitada'");
    }
};
