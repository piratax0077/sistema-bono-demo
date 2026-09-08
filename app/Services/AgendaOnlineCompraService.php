<?php

namespace App\Services;

use App\Models\AgendaOnlineHorario;
use App\Models\PersonaBusqueda;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherPago;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AgendaOnlineCompraService
{
    public function comprar(User $user, int $horarioId, string $rutIngresado, string $ip): Voucher
    {
        return DB::transaction(function () use ($user, $horarioId, $rutIngresado, $ip) {
            $horario = AgendaOnlineHorario::with(['profesional', 'servicio'])
                ->lockForUpdate()->findOrFail($horarioId);
            if ($horario->estado !== 'disponible' || ! $horario->fecha_hora->isFuture()) {
                throw new RuntimeException('La hora seleccionada ya no está disponible. Elija otro horario.');
            }

            $rut = $this->normalizarRut($rutIngresado);
            if (! $this->rutValido($rut)) {
                throw new RuntimeException('RUT no válido. Revise el número y el dígito verificador.');
            }
            if (! hash_equals($this->normalizarRut($user->rut), $rut)) {
                throw new RuntimeException('Usuario no encontrado. Comuníquese con su sistema de previsión.');
            }
            $persona = PersonaBusqueda::porRut($rut)->first();
            if (! $persona) {
                throw new RuntimeException('Usuario no encontrado. Comuníquese con su sistema de previsión.');
            }

            $baseUsuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($rut))->where('rut_sha256', $this->rutSha($rut)))->first();
            $baseProfesional = $this->vigente(VoucherBaseProfesional::where('rut_hash', $this->rutHmac($horario->profesional->rut))->where('rut_sha256', $this->rutSha($horario->profesional->rut)))->first();
            $baseServicio = $this->vigente(VoucherBaseServicio::where('otros->voucher_servicio_id', $horario->servicio_id))->first();
            if (! $baseUsuario || ! $baseProfesional || ! $baseServicio) {
                throw new RuntimeException('No fue posible validar el convenio previsional para esta hora.');
            }
            $relacion = $this->vigente(VoucherBaseRelacion::where('usuario_id', $baseUsuario->id)
                ->where('profesional_id', $baseProfesional->id)->where('servicio_id', $baseServicio->id)
                ->where('estado', 'vigente'))->first();
            if (! $relacion || $relacion->requiere_auditoria) {
                throw new RuntimeException('El convenio no permite emitir automáticamente este bono.');
            }

            $valor = (float) (data_get($relacion->otros, 'valor_convenio') ?? $baseServicio->valor_referencial ?? $horario->servicio->valor_base);
            $copago = (float) (data_get($relacion->otros, 'copago_convenio') ?? $horario->servicio->copago_base);
            $codigo = 'BONO-ONL-'.now()->format('ymd').'-'.Str::upper(Str::random(7));
            $voucher = Voucher::create([
                'codigo' => $codigo, 'qr_token' => Str::random(80), 'qr_expira' => now()->addDays(30), 'qr_usado' => false,
                'cliente_id' => $user->id, 'cliente_rut' => Crypt::encryptString($rut), 'cliente_rut_hash' => hash('sha256', $rut),
                'cliente_nombre' => $persona->nombre_completo ?: $user->name, 'cliente_email' => $user->email, 'cliente_telefono' => $user->telefono,
                'beneficiario_tipo' => 'titular', 'beneficiario_base_usuario_id' => $baseUsuario->id,
                'beneficiario_nombre' => $baseUsuario->nombre, 'beneficiario_rut' => Crypt::encryptString($rut),
                'beneficiario_rut_hash' => $this->rutHmac($rut), 'beneficiario_direccion' => $baseUsuario->direccion_encrypted,
                'beneficiario_fecha_nacimiento' => $baseUsuario->fecha_nacimiento_encrypted, 'beneficiario_edad' => data_get($baseUsuario->otros, 'edad'),
                'tipo_servicio' => $horario->servicio->nombre, 'servicio_id' => $horario->servicio_id,
                'valor' => $valor, 'valor_total' => $valor, 'copago_usuario' => $copago,
                'saldo_veterinario' => max($valor - $copago - (float) $horario->servicio->comision_veterchile, 0),
                'comision_veterchile' => $horario->servicio->comision_veterchile, 'porcentaje_descuento' => 100,
                'estado' => 'activo', 'fecha_vencimiento' => now()->addDays(30), 'cliente_aceptado_en' => now(), 'otp_validado_at' => now(),
                'profesional_id' => $horario->profesional_id, 'prestador_rut' => $horario->profesional->rut,
                'prestador_nombre' => $horario->profesional->nombre, 'prestador_especialidad' => $horario->profesional->especialidad,
                'prestador_email' => $horario->profesional->email, 'prestador_telefono' => $horario->profesional->telefono,
                'prestador_direccion' => trim($horario->centro_nombre.' · '.$horario->centro_direccion.' · '.$horario->lugar_atencion),
            ]);
            $voucher->update(['qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))]);

            VoucherPago::create([
                'voucher_id' => $voucher->id, 'monto_pagado_usuario' => $copago,
                'metodo_pago' => 'tarjeta_demo_online', 'estado_pago' => 'pagado',
                'comprobante' => 'PAGO-DEMO-'.now()->format('YmdHis').'-'.$voucher->id,
            ]);

            $medichile = DB::connection('medichile');
            $pacienteMedichile = $medichile->table('pacientes')->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(rut,'.',''),'-',''),' ','')) = ?", [$rut])->first();
            $profesionalMedichile = $medichile->table('profesionales')->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(rut,'.',''),'-',''),' ','')) = ?", [$this->normalizarRut($horario->profesional->rut)])->first();
            if (! $pacienteMedichile || ! $profesionalMedichile) {
                throw new RuntimeException('La agenda Medichile no reconoce al paciente o profesional.');
            }
            $inicio = $horario->fecha_hora->copy();
            $horaMedichileId = $medichile->table('horas_medicas')->insertGetId([
                'fecha_consulta' => $inicio->toDateString(), 'hora_inicio' => $inicio->format('H:i:s'),
                'hora_termino' => $inicio->copy()->addMinutes($horario->duracion_minutos)->format('H:i:s'),
                'descripcion' => 'SDI-'.$voucher->codigo,
                'observaciones' => 'Reserva online con bono pagado. '.$horario->centro_nombre.' '.$horario->lugar_atencion,
                'id_profesional' => $profesionalMedichile->id, 'id_paciente' => $pacienteMedichile->id,
                'id_estado' => 2, 'created_at' => now(), 'updated_at' => now(),
            ]);

            $agenda = VoucherAgenda::create([
                'voucher_id' => $voucher->id, 'cliente_id' => $user->id, 'profesional_id' => $horario->profesional_id,
                'fecha_hora_solicitada' => $horario->fecha_hora, 'fecha_hora_confirmada' => $horario->fecha_hora,
                'estado' => 'hora_confirmada',
                'observacion' => 'Reserva online pagada. '.$horario->centro_nombre.' · '.$horario->lugar_atencion,
                'medichile_hora_medica_id' => $horaMedichileId, 'medichile_estado_id' => 2,
                'medichile_sincronizado_at' => now(), 'medichile_sync_error' => null,
            ]);
            $voucher->update(['agenda_id' => $agenda->id]);
            $horario->update(['estado' => 'reservado', 'reservado_por' => $user->id, 'voucher_id' => $voucher->id]);

            $qrUrl = route('vouchers.usar', $voucher->qr_token);
            $mensaje = 'Bono '.$voucher->codigo.' · '.$persona->nombre_completo.' · '.$horario->profesional->nombre.' · '.$horario->fecha_hora->format('d-m-Y H:i').' · '.$horario->centro_nombre.'. QR: '.$qrUrl;
            foreach ([
                ['patient_whatsapp', 'WhatsApp paciente', $user->telefono, ['copia' => 'paciente']],
                ['medical_center_whatsapp', 'Centro médico / consulta', $horario->centro_telefono ?: $horario->centro_email, ['centro' => $horario->centro_nombre]],
                ['assistant_totem_reception', 'Recepción asistente / tótem', $horario->centro_nombre, ['estado_paciente' => 'pendiente_llegada', 'hora_medichile_id' => $horaMedichileId]],
            ] as [$canal, $tipo, $destino, $metadata]) {
                VoucherDeliveryRequest::create([
                    'voucher_id' => $voucher->id, 'cliente_user_id' => $user->id, 'canal' => $canal,
                    'destino_tipo' => $tipo, 'destino' => $destino, 'estado' => 'prepared',
                    'mensaje' => $mensaje, 'action_url' => $qrUrl, 'enviado_en' => now(),
                    'metadata' => array_merge($metadata, ['origen' => 'agenda_online', 'lugar' => $horario->lugar_atencion]),
                ]);
            }
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id, 'accion' => 'agenda_online_bono_pagado_qr_emitido',
                'usuario_tipo' => 'cliente', 'usuario_id' => $user->id,
                'descripcion' => 'Hora online reservada, copago simulado pagado, QR generado y enviado a paciente, centro y bandeja de asistente. Hora Medichile #'.$horaMedichileId.'.',
                'ip' => $ip,
            ]);

            return $voucher->fresh(['agenda', 'pagos', 'profesional']);
        });
    }

    private function vigente($query) { return $query->whereIn('estado', ['activo','vigente'])->where(fn($q) => $q->whereNull('vigente_desde')->orWhere('vigente_desde','<=',now()->toDateString()))->where(fn($q) => $q->whereNull('vigente_hasta')->orWhere('vigente_hasta','>=',now()->toDateString())); }
    private function normalizarRut($rut): string { return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut)); }
    private function rutHmac($rut): string { return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key')); }
    private function rutSha($rut): string { return hash('sha256', $this->normalizarRut($rut)); }
    private function rutValido(string $rut): bool { if(!preg_match('/^(\d{7,8})([0-9K])$/',$rut,$p)) return false; $s=0;$m=2;for($i=strlen($p[1])-1;$i>=0;$i--){$s+=((int)$p[1][$i])*$m;$m=$m===7?2:$m+1;} $r=11-($s%11);$dv=$r===11?'0':($r===10?'K':(string)$r);return hash_equals($dv,$p[2]); }
}
