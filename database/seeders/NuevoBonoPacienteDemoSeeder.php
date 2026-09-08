<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherPago;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class NuevoBonoPacienteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $codigo = 'DEMO-PACIENTE-002';
        $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
        $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
        $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);
        $servicio = VoucherServicio::where('activo', true)->orderBy('id')->firstOrFail();

        $voucher = Voucher::updateOrCreate(['codigo' => $codigo], [
            'qr_token' => 'demo-paciente-002-'.Str::lower(Str::random(40)),
            'qr_expira' => now()->addDays(30),
            'qr_usado' => false,
            'qr_usado_at' => null,
            'cliente_id' => $paciente->id,
            'cliente_rut' => Crypt::encryptString($paciente->rut),
            'cliente_rut_hash' => hash('sha256', preg_replace('/\D+/', '', $paciente->rut)),
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
            'comprobante' => 'DEMO-PACIENTE-002-PAGO',
        ]);

        $medichile = DB::connection('medichile');
        $pacienteMedichile = $this->buscarRut($medichile, 'pacientes', '102115686');
        $profesionalMedichile = $this->buscarRut($medichile, 'profesionales', '111111111');

        if (! $pacienteMedichile || ! $profesionalMedichile) {
            throw new RuntimeException('Falta el paciente o profesional demo en Medichile.');
        }

        $descripcion = 'SDI-'.$codigo;
        $inicio = now()->addMinutes(30)->startOfMinute();
        $hora = $medichile->table('horas_medicas')->where('descripcion', $descripcion)->first();
        $datosHora = [
            'fecha_consulta' => now()->toDateString(),
            'hora_inicio' => $inicio->format('H:i:s'),
            'hora_termino' => $inicio->copy()->addMinutes(30)->format('H:i:s'),
            'descripcion' => $descripcion,
            'observaciones' => 'Hora confirmada para probar recepción del bono '.$codigo,
            'id_profesional' => $profesionalMedichile->id,
            'id_paciente' => $pacienteMedichile->id,
            'id_estado' => 2,
            'updated_at' => now(),
        ];

        if ($hora) {
            $medichile->table('horas_medicas')->where('id', $hora->id)->update($datosHora);
            $horaId = $hora->id;
        } else {
            $datosHora['created_at'] = now();
            $horaId = $medichile->table('horas_medicas')->insertGetId($datosHora);
        }

        $agenda = VoucherAgenda::updateOrCreate(['voucher_id' => $voucher->id], [
            'cliente_id' => $paciente->id,
            'profesional_id' => $profesional->id,
            'fecha_hora_solicitada' => $inicio,
            'fecha_hora_confirmada' => $inicio,
            'estado' => 'hora_confirmada',
            'observacion' => 'Hora confirmada; paciente pendiente de recepción.',
            'medichile_hora_medica_id' => $horaId,
            'medichile_estado_id' => 2,
            'medichile_sincronizado_at' => now(),
            'medichile_sync_error' => null,
        ]);
        $voucher->update(['agenda_id' => $agenda->id]);

        $qrUrl = route('vouchers.usar', $voucher->qr_token);
        $mensaje = 'SDI: bono '.$codigo.' disponible. QR seguro: '.$qrUrl;

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
            'mensaje' => 'Paciente con bono asociado y hora Medichile #'.$horaId.' confirmada; pendiente de llegada.',
            'action_url' => route('vouchers.validarPantalla', $voucher->qr_token),
            'enviado_en' => now(),
            'metadata' => [
                'demo' => true,
                'estado_paciente' => 'pendiente_recepcion',
                'medichile_hora_medica_id' => $horaId,
                'medichile_estado' => 'Confirmada',
            ],
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'demo_nuevo_bono_comprado_hora_confirmada',
            'usuario_tipo' => 'cliente',
            'usuario_id' => $paciente->id,
            'descripcion' => 'Nuevo bono comprado y pagado. Hora Medichile #'.$horaId.' confirmada; pendiente de recepción.',
            'ip' => '127.0.0.1',
        ]);
    }

    private function buscarRut($conexion, string $tabla, string $rut)
    {
        return $conexion->table($tabla)
            ->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?", [$rut])
            ->first();
    }
}
