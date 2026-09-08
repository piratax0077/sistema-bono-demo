<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherPago;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoPacientePendienteSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
            $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
            $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);
            $servicio = VoucherServicio::where('activo', true)->orderBy('id')->firstOrFail();

            $voucher = Voucher::updateOrCreate(['codigo' => 'DEMO-PACIENTE-PRUEBA'], [
                'qr_token' => 'demo-paciente-'.Str::lower(Str::random(40)),
                'qr_expira' => now()->addDays(30),
                'qr_usado' => false,
                'qr_usado_at' => null,
                'cliente_id' => $paciente->id,
                'cliente_rut' => Crypt::encryptString($paciente->rut ?: '111111111'),
                'cliente_rut_hash' => hash('sha256', preg_replace('/\D+/', '', $paciente->rut ?: '111111111')),
                'cliente_nombre' => $paciente->name,
                'cliente_email' => $paciente->email,
                'cliente_telefono' => $paciente->telefono,
                'beneficiario_tipo' => 'titular',
                'beneficiario_nombre' => $paciente->name,
                'tipo_servicio' => $servicio->nombre,
                'servicio_id' => $servicio->id,
                'profesional_id' => $profesional->id,
                'profesional_atendio_id' => null,
                'prestador_rut' => $profesional->rut,
                'prestador_nombre' => $profesional->nombre,
                'prestador_especialidad' => $profesional->especialidad,
                'prestador_email' => $profesional->email,
                'prestador_telefono' => $profesional->telefono,
                'valor' => $servicio->valor_base ?: 25000,
                'valor_total' => $servicio->valor_base ?: 25000,
                'copago_usuario' => $servicio->copago_base ?: 5000,
                'saldo_veterinario' => max(($servicio->valor_base ?: 25000) - ($servicio->copago_base ?: 5000) - ($servicio->comision_veterchile ?: 0), 0),
                'comision_veterchile' => $servicio->comision_veterchile ?: 0,
                'porcentaje_descuento' => 100,
                'estado' => 'activo',
                'fecha_vencimiento' => now()->addDays(30),
                'cliente_aceptado_en' => now(),
                'otp_validado_at' => now(),
                'usado_en' => null,
                'agenda_id' => null,
                'atencion_id' => null,
                'atencion_cerrada_at' => null,
                'validado_at' => null,
                'estado_validacion' => null,
                'riesgo_validacion' => null,
            ]);

            $voucher->update([
                'qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key')),
            ]);

            $voucher->cobros()->delete();
            $voucher->pagos()->delete();
            VoucherDeliveryRequest::where('voucher_id', $voucher->id)->delete();
            VoucherAuditoria::where('voucher_id', $voucher->id)->delete();

            VoucherPago::create([
                'voucher_id' => $voucher->id,
                'monto_pagado_usuario' => $voucher->copago_usuario,
                'metodo_pago' => 'tarjeta_demo',
                'estado_pago' => 'pagado',
                'comprobante' => 'DEMO-PACIENTE-PAGO',
            ]);

            $qrUrl = route('vouchers.usar', $voucher->qr_token);
            $mensaje = 'SDI: bono '.$voucher->codigo.' disponible. QR seguro: '.$qrUrl;

            VoucherDeliveryRequest::create([
                'voucher_id' => $voucher->id,
                'cliente_user_id' => $paciente->id,
                'canal' => 'patient_whatsapp',
                'destino_tipo' => 'WhatsApp paciente',
                'destino' => preg_replace('/\D+/', '', $paciente->telefono),
                'estado' => 'prepared',
                'mensaje' => $mensaje,
                'action_url' => $qrUrl,
                'enviado_en' => now(),
                'metadata' => ['demo' => true, 'etapa' => 'compra_y_envio'],
            ]);

            VoucherDeliveryRequest::create([
                'voucher_id' => $voucher->id,
                'cliente_user_id' => $paciente->id,
                'canal' => 'assistant_totem_reception',
                'destino_tipo' => 'Recepción asistente / tótem',
                'destino' => 'Centro Médico de Prueba',
                'estado' => 'prepared',
                'mensaje' => 'Paciente con bono asociado pendiente de llegada. '.$mensaje,
                'action_url' => route('vouchers.validarPantalla', $voucher->qr_token),
                'enviado_en' => now(),
                'metadata' => ['demo' => true, 'estado_paciente' => 'pendiente_recepcion'],
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'demo_paciente_compra_iniciada',
                'usuario_tipo' => 'cliente',
                'usuario_id' => $paciente->id,
                'descripcion' => 'Bono nuevo de prueba: comprado, pagado y enviado; pendiente de recepción del paciente.',
                'ip' => '127.0.0.1',
            ]);
        });
    }
}
