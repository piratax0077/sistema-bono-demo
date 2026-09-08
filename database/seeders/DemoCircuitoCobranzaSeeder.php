<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAtencion;
use App\Models\VoucherAuditoria;
use App\Models\VoucherCobro;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherLiquidacion;
use App\Models\VoucherPago;
use App\Models\VoucherProfesional;
use App\Models\VoucherRendicion;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoCircuitoCobranzaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
            $profUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
            $asistente = User::where('email', 'asistente@gmail.com')->firstOrFail();
            $admin = User::where('email', 'administrador@gmail.com')->firstOrFail();
            $contralor = User::where('email', 'contralor@gmail.com')->firstOrFail();
            $profesional = VoucherProfesional::findOrFail($profUser->profesional_id);
            $servicios = VoucherServicio::whereIn('nombre', ['Consulta medicina general', 'Consulta cardiología'])->get()->keyBy('nombre');

            $rendicion = VoucherRendicion::updateOrCreate(['veterinario_nombre' => 'LOTE DEMO CENTRO MEDICO DE PRUEBA'], [
                'sucursal' => 'Centro Médico de Prueba', 'total_cobrado' => 60000,
                'cantidad_vouchers' => 2, 'estado' => 'pagada',
                'rendida_en' => now()->subMinutes(15), 'pagada_en' => now()->subMinutes(5),
            ]);

            foreach ([
                ['codigo' => 'DEMO-COBRO-001', 'servicio' => 'Consulta medicina general', 'valor' => 25000, 'copago' => 5000, 'saldo' => 20000],
                ['codigo' => 'DEMO-COBRO-002', 'servicio' => 'Consulta cardiología', 'valor' => 42000, 'copago' => 9000, 'saldo' => 33000],
            ] as $index => $item) {
                $servicio = $servicios->get($item['servicio']) ?: VoucherServicio::where('activo', true)->firstOrFail();
                $voucher = Voucher::updateOrCreate(['codigo' => $item['codigo']], [
                    'qr_token' => 'demo-cobro-'.Str::lower(Str::random(48)),
                    'cliente_id' => $paciente->id, 'cliente_rut' => $paciente->rut,
                    'cliente_rut_hash' => hash('sha256', preg_replace('/[^0-9K]/i', '', $paciente->rut)),
                    'cliente_nombre' => $paciente->name, 'cliente_telefono' => $paciente->telefono,
                    'cliente_email' => $paciente->email, 'tipo_servicio' => $item['servicio'],
                    'servicio_id' => $servicio->id, 'profesional_id' => $profesional->id,
                    'profesional_atendio_id' => $profesional->id, 'asistente_valido_id' => $asistente->id,
                    'prestador_rut' => $profesional->rut, 'prestador_nombre' => $profesional->nombre,
                    'prestador_especialidad' => $servicio->nombre, 'prestador_email' => $profesional->email,
                    'prestador_telefono' => $profesional->telefono, 'prestador_direccion' => 'Centro Médico de Prueba, Box '.($index + 1),
                    'valor' => $item['valor'], 'valor_total' => $item['valor'],
                    'copago_usuario' => $item['copago'], 'saldo_veterinario' => $item['saldo'],
                    'comision_veterchile' => 1000, 'estado' => 'cobrado',
                    'fecha_vencimiento' => now()->addMonth(), 'qr_usado' => true,
                    'qr_usado_at' => now()->subMinutes(20), 'usado_en' => now()->subMinutes(20),
                    'atencion_cerrada_at' => now()->subMinutes(35), 'validado_at' => now()->subMinutes(30),
                    'estado_validacion' => 'validada_por_asistente', 'riesgo_validacion' => 'bajo',
                ]);
                $voucher->update(['qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))]);

                VoucherPago::updateOrCreate(['voucher_id' => $voucher->id], [
                    'monto_pagado_usuario' => $item['copago'], 'metodo_pago' => 'tarjeta_demo',
                    'estado_pago' => 'pagado', 'comprobante' => 'COMPRA-'.$item['codigo'],
                ]);
                VoucherDeliveryRequest::updateOrCreate(['voucher_id' => $voucher->id, 'canal' => 'patient_whatsapp'], [
                    'cliente_user_id' => $paciente->id, 'destino_tipo' => 'WhatsApp paciente',
                    'destino' => $paciente->telefono, 'estado' => 'received',
                    'mensaje' => 'QR enviado al paciente y recibido en Centro Médico de Prueba.',
                    'enviado_en' => now()->subHour(), 'metadata' => ['demo' => true, 'centro' => 'Centro Médico de Prueba'],
                ]);
                VoucherDeliveryRequest::updateOrCreate(['voucher_id' => $voucher->id, 'canal' => 'assistant_totem_reception'], [
                    'cliente_user_id' => $paciente->id, 'destino_tipo' => 'Recepción asistente / tótem',
                    'destino' => 'Centro Médico de Prueba, Box '.($index + 1), 'estado' => 'received',
                    'mensaje' => 'Paciente recibido y dejado en espera.', 'enviado_en' => now()->subMinutes(50),
                    'metadata' => ['estado_paciente' => 'atendido', 'recibido_por_user_id' => $asistente->id],
                ]);
                $agenda = VoucherAgenda::updateOrCreate(['voucher_id' => $voucher->id], [
                    'cliente_id' => $paciente->id, 'profesional_id' => $profesional->id,
                    'fecha_hora_solicitada' => now()->subHour(), 'fecha_hora_confirmada' => now()->subHour(),
                    'estado' => 'atencion_realizada', 'observacion' => 'Flujo de prueba completado.',
                ]);
                $atencion = VoucherAtencion::updateOrCreate(['voucher_id' => $voucher->id], [
                    'agenda_id' => $agenda->id, 'cliente_id' => $paciente->id,
                    'profesional_id' => $profesional->id, 'asistente_id' => $asistente->id,
                    'inicio_atencion' => now()->subMinutes(45), 'fin_atencion' => now()->subMinutes(35),
                    'cerrada_at' => now()->subMinutes(35), 'validada_at' => now()->subMinutes(30),
                    'estado' => 'validada_por_asistente', 'riesgo' => 'bajo',
                    'hash_auditoria' => hash('sha256', $item['codigo'].'|atencion|validada'),
                ]);
                $voucher->update(['agenda_id' => $agenda->id, 'atencion_id' => $atencion->id]);
                VoucherCobro::updateOrCreate(['voucher_id' => $voucher->id], [
                    'profesional_id' => $profesional->id, 'voucher_rendicion_id' => $rendicion->id,
                    'veterinario_nombre' => $profesional->nombre, 'sucursal' => 'Centro Médico de Prueba',
                    'monto_cobrado' => $item['saldo'], 'estado' => 'pagado', 'cobrado_en' => now()->subMinutes(20),
                ]);

                VoucherAuditoria::where('voucher_id', $voucher->id)->where('accion', 'like', 'circuito_demo_%')->delete();
                foreach ([
                    ['circuito_demo_compra', 'cliente', $paciente->id, 'Paciente compró el bono para Profesional Revisión.'],
                    ['circuito_demo_envio_recepcion', 'asistente', $asistente->id, 'QR enviado y paciente recibido en Centro Médico de Prueba.'],
                    ['circuito_demo_atencion', 'profesional', $profUser->id, 'Profesional realizó y cerró la atención.'],
                    ['circuito_demo_cobro', 'profesional', $profUser->id, 'Profesional envió el bono a cobro.'],
                    ['circuito_demo_administracion', 'admin', $admin->id, 'Administración consolidó el bono en una rendición.'],
                    ['circuito_demo_contraloria', 'auditor', $contralor->id, 'Expediente conservado y disponible para contraloría y futuros cobros.'],
                ] as [$accion, $tipo, $usuarioId, $detalle]) {
                    VoucherAuditoria::create(['voucher_id' => $voucher->id, 'accion' => $accion, 'usuario_tipo' => $tipo, 'usuario_id' => $usuarioId, 'descripcion' => $detalle, 'ip' => '127.0.0.1']);
                }
            }

            VoucherLiquidacion::updateOrCreate(['voucher_rendicion_id' => $rendicion->id], [
                'profesional_id' => $profesional->id, 'profesional_nombre' => $profesional->nombre,
                'banco' => $profesional->banco, 'tipo_cuenta' => $profesional->tipo_cuenta,
                'numero_cuenta' => $profesional->numero_cuenta, 'monto_profesional' => 53000,
                'comision_veterchile' => 2000, 'estado' => 'pagada',
                'medio_pago' => 'transferencia_bancaria_demo', 'comprobante_transferencia' => 'DEPOSITO-LOTE-DEMO-001',
                'pagado_en' => now()->subMinutes(5),
            ]);
        });
    }
}
