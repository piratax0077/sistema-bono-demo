<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoFlujoBonoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
            $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
            $asistente = User::where('email', 'asistente@gmail.com')->firstOrFail();
            $contralor = User::where('email', 'contralor@gmail.com')->firstOrFail();
            $admin = User::where('email', 'administrador@gmail.com')->firstOrFail();
            $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);
            $servicio = VoucherServicio::firstOrCreate(
                ['nombre' => 'Consulta médica demo'],
                [
                    'descripcion' => 'Servicio para demostración integral del flujo del bono.',
                    'valor_base' => 25000,
                    'copago_base' => 5000,
                    'comision_veterchile' => 1000,
                    'activo' => true,
                ]
            );

            $voucher = Voucher::updateOrCreate(['codigo' => 'DEMO-FLUJO-001'], [
                'qr_token' => 'demo-flujo-'.Str::lower(Str::random(32)),
                'cliente_id' => $paciente->id,
                'cliente_rut' => Crypt::encryptString('111111111'),
                'cliente_rut_hash' => hash('sha256', '111111111'),
                'cliente_nombre' => $paciente->name,
                'cliente_email' => $paciente->email,
                'cliente_telefono' => '+56900000000',
                'beneficiario_tipo' => 'titular',
                'beneficiario_nombre' => $paciente->name,
                'tipo_servicio' => $servicio->nombre,
                'servicio_id' => $servicio->id,
                'profesional_id' => $profesional->id,
                'profesional_atendio_id' => $profesional->id,
                'prestador_nombre' => $profesional->nombre,
                'prestador_especialidad' => $profesional->especialidad,
                'valor' => 25000,
                'valor_total' => 25000,
                'copago_usuario' => 5000,
                'saldo_veterinario' => 19000,
                'comision_veterchile' => 1000,
                'porcentaje_descuento' => 80,
                'estado' => 'cobrado',
                'fecha_vencimiento' => now()->addMonth(),
                'cliente_aceptado_en' => now()->subMinutes(58),
                'otp_validado_at' => now()->subMinutes(57),
                'qr_usado' => true,
                'qr_usado_at' => now()->subMinutes(20),
                'usado_en' => now()->subMinutes(20),
                'atencion_cerrada_at' => now()->subMinutes(30),
                'validado_at' => now()->subMinutes(25),
                'ip_profesional' => '127.0.0.1',
                'ip_asistente' => '127.0.0.2',
                'estado_validacion' => 'validada_por_asistente',
                'riesgo_validacion' => 'bajo',
            ]);

            $voucher->update(['qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))]);

            VoucherPago::updateOrCreate(['voucher_id' => $voucher->id], [
                'monto_pagado_usuario' => 5000,
                'metodo_pago' => 'tarjeta_demo',
                'estado_pago' => 'pagado',
                'comprobante' => 'DEMO-COMPRA-001',
            ]);

            VoucherDeliveryRequest::updateOrCreate(
                ['voucher_id' => $voucher->id, 'canal' => 'email'],
                [
                    'cliente_user_id' => $paciente->id,
                    'destino_tipo' => 'paciente',
                    'destino' => $paciente->email,
                    'estado' => 'received',
                    'mensaje' => 'QR demo enviado y recibido por el paciente.',
                    'action_url' => route('vouchers.qr', $voucher->qr_token),
                    'enviado_en' => now()->subMinutes(55),
                    'metadata' => ['demo' => true, 'recibido_en' => now()->subMinutes(54)->toIso8601String()],
                ]
            );

            $atencion = VoucherAtencion::updateOrCreate(['voucher_id' => $voucher->id], [
                'cliente_id' => $paciente->id,
                'profesional_id' => $profesional->id,
                'asistente_id' => $asistente->id,
                'inicio_atencion' => now()->subMinutes(45),
                'fin_atencion' => now()->subMinutes(30),
                'cerrada_at' => now()->subMinutes(30),
                'validada_at' => now()->subMinutes(25),
                'ip_profesional' => '127.0.0.1',
                'ip_asistente' => '127.0.0.2',
                'estado' => 'validada_por_asistente',
                'riesgo' => 'bajo',
                'hash_auditoria' => hash('sha256', 'DEMO-FLUJO-001|atencion|validada'),
                'observacion' => 'Atención demo cerrada y validada.',
            ]);
            $voucher->update(['atencion_id' => $atencion->id]);

            $rendicion = VoucherRendicion::updateOrCreate(['veterinario_nombre' => 'DEMO '.$profesional->nombre], [
                'sucursal' => 'Sucursal Demo',
                'total_cobrado' => 19000,
                'cantidad_vouchers' => 1,
                'estado' => 'pagada',
                'rendida_en' => now()->subMinutes(15),
                'pagada_en' => now()->subMinutes(5),
            ]);

            VoucherCobro::updateOrCreate(['voucher_id' => $voucher->id], [
                'profesional_id' => $profesional->id,
                'voucher_rendicion_id' => $rendicion->id,
                'veterinario_nombre' => $profesional->nombre,
                'sucursal' => 'Sucursal Demo',
                'monto_cobrado' => 19000,
                'estado' => 'pagado',
                'cobrado_en' => now()->subMinutes(20),
            ]);

            VoucherLiquidacion::updateOrCreate(['voucher_rendicion_id' => $rendicion->id], [
                'profesional_id' => $profesional->id,
                'profesional_nombre' => $profesional->nombre,
                'banco' => 'Banco Demo',
                'tipo_cuenta' => 'Cuenta corriente',
                'numero_cuenta' => '00000001',
                'monto_profesional' => 19000,
                'comision_veterchile' => 1000,
                'estado' => 'pagada',
                'medio_pago' => 'transferencia_demo',
                'comprobante_transferencia' => 'DEMO-PAGO-001',
                'pagado_en' => now()->subMinutes(5),
            ]);

            VoucherAuditoria::where('voucher_id', $voucher->id)->where('accion', 'like', 'demo_%')->delete();
            foreach ([
                ['demo_compra_pagada', 'cliente', $paciente->id, 'Paciente compró y pagó el copago del bono.'],
                ['demo_qr_enviado', 'sistema', null, 'QR firmado enviado por email al paciente.'],
                ['demo_qr_recibido', 'cliente', $paciente->id, 'Paciente recibió el QR y mantuvo el bono vigente.'],
                ['demo_atencion_cerrada', 'profesional', $profesionalUser->id, 'Profesional cerró la atención clínica.'],
                ['demo_bono_habilitado_cobro', 'asistente', $asistente->id, 'Asistente validó la atención; bono habilitado para cobro.'],
                ['demo_auditoria_aprobada', 'auditor', $contralor->id, 'Contralor revisó trazabilidad, actores y montos.'],
                ['demo_cobro_rendido', 'profesional', $profesionalUser->id, 'Cobro incorporado a rendición del profesional.'],
                ['demo_liquidacion_pagada', 'admin', $admin->id, 'Administrador pagó la liquidación al profesional.'],
            ] as [$accion, $tipo, $usuarioId, $descripcion]) {
                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => $accion,
                    'usuario_tipo' => $tipo,
                    'usuario_id' => $usuarioId,
                    'descripcion' => $descripcion,
                    'ip' => '127.0.0.1',
                ]);
            }
        });
    }
}
