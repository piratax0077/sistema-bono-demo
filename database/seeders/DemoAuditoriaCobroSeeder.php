<?php

namespace Database\Seeders;

use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAtencion;
use App\Models\VoucherAuditoria;
use App\Models\VoucherCobro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoAuditoriaCobroSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $base = Voucher::with(['agenda', 'atencion'])
                ->where('codigo', 'DEMO-PACIENTE-PRUEBA')
                ->firstOrFail();

            $voucher = Voucher::firstOrNew(['codigo' => 'DEMO-AUDITORIA-001']);
            if (! $voucher->exists) {
                $atributos = collect($base->getAttributes())->except([
                    'id', 'codigo', 'qr_token', 'qr_firma', 'created_at', 'updated_at',
                    'agenda_id', 'atencion_id',
                ])->all();
                $voucher->forceFill($atributos);
                $voucher->codigo = 'DEMO-AUDITORIA-001';
                $voucher->qr_token = Str::random(80);
                $voucher->estado = 'cobrado';
                $voucher->qr_usado = true;
                $voucher->qr_usado_at = now();
                $voucher->usado_en = now();
                $voucher->atencion_cerrada_at = now()->subMinutes(10);
                $voucher->validado_at = now()->subMinutes(9);
                $voucher->estado_validacion = 'validada_automaticamente';
                $voucher->save();
                $voucher->update([
                    'qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key')),
                ]);
            }

            $agendaBase = $base->agenda;
            VoucherAgenda::updateOrCreate(['voucher_id' => $voucher->id], [
                'cliente_id' => $voucher->cliente_id,
                'profesional_id' => $voucher->profesional_id,
                'fecha_hora_solicitada' => optional($agendaBase)->fecha_hora_solicitada ?: now()->subHour(),
                'fecha_hora_confirmada' => optional($agendaBase)->fecha_hora_confirmada ?: now()->subHour(),
                'estado' => 'atencion_realizada',
                'observacion' => 'Expediente demo para visto bueno de contraloría.',
                'medichile_hora_medica_id' => optional($agendaBase)->medichile_hora_medica_id,
                'medichile_estado_id' => 6,
                'medichile_sincronizado_at' => now(),
            ]);
            $agenda = VoucherAgenda::where('voucher_id', $voucher->id)->firstOrFail();

            VoucherAtencion::updateOrCreate(['voucher_id' => $voucher->id], [
                'agenda_id' => $agenda->id,
                'cliente_id' => $voucher->cliente_id,
                'profesional_id' => $voucher->profesional_id,
                'inicio_atencion' => now()->subMinutes(25),
                'fin_atencion' => now()->subMinutes(10),
                'cerrada_at' => now()->subMinutes(10),
                'validada_at' => now()->subMinutes(9),
                'estado' => 'validada_automaticamente',
                'riesgo' => 'bajo',
                'diagnostico' => 'Diagnóstico de prueba auditado; paciente estable.',
                'direccion' => $voucher->prestador_direccion ?: 'Centro médico de prueba',
                'hash_auditoria' => hash('sha256', $voucher->id.'|demo-auditoria'),
            ]);

            VoucherCobro::updateOrCreate(['voucher_id' => $voucher->id], [
                'profesional_id' => $voucher->profesional_id,
                'veterinario_nombre' => $voucher->prestador_nombre,
                'sucursal' => $voucher->prestador_direccion ?: 'Centro médico de prueba',
                'monto_cobrado' => $voucher->saldo_veterinario,
                'estado' => 'pendiente_auditoria',
                'cobrado_en' => now(),
                'voucher_rendicion_id' => null,
                'auditor_id' => null,
                'auditado_at' => null,
                'observacion_auditor' => null,
                'resultado_controles' => null,
                'hash_visto_bueno' => null,
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'qr_cobro_demo_enviado_auditoria',
                'usuario_tipo' => 'profesional',
                'descripcion' => 'Expediente demo creado para probar el visto bueno de contraloría.',
                'ip' => '127.0.0.1',
            ]);
        });
    }
}
