<?php

namespace App\Services;

use App\Models\PersonaBusqueda;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AgendaExternaCompraService
{
    public function __construct(private MedsdiAgendaApiService $medsdiApi)
    {
    }

    public function comprar(User $user, array $seleccion, string $rutIngresado, string $ip): Voucher
    {
        return DB::transaction(function () use ($user, $seleccion, $rutIngresado, $ip) {
            $perfilRemoto = $this->medsdiApi->pacienteAutenticado();
            if (! $perfilRemoto['ok']) {
                throw new RuntimeException('No fue posible validar el paciente autenticado en Med-SDI: '.$perfilRemoto['mensaje']);
            }
            $pacienteMedsdi = $perfilRemoto['paciente'];
            $rut = $this->normalizarRut($rutIngresado);
            if (! $this->rutValido($rut)) {
                throw new RuntimeException('RUT no válido. Revise el número y el dígito verificador.');
            }
            if (! hash_equals($this->normalizarRut($pacienteMedsdi['rut'] ?? ''), $rut)) {
                throw new RuntimeException('El paciente seleccionado no coincide con el usuario autenticado en Med-SDI.');
            }

            $persona = PersonaBusqueda::porRut($rut)->first();
            $fechaHora = Carbon::parse($seleccion['fecha_hora']);
            if ($fechaHora->isPast()) {
                throw new RuntimeException('La hora seleccionada ya no está disponible. Elija otro horario.');
            }

            $cotizacionRemota = $this->medsdiApi->cotizar([
                'id_profesional' => $seleccion['id_profesional'],
                'id_lugar_atencion' => $seleccion['id_lugar'],
                'id_prestacion' => $seleccion['id_prestacion'],
                'origen_prestacion' => $seleccion['origen_prestacion'],
            ]);
            if (! $cotizacionRemota['ok']) {
                throw new RuntimeException('No fue posible confirmar la cotización: '.$cotizacionRemota['mensaje']);
            }
            $cotizacion = $cotizacionRemota['cotizacion'];

            $reservaReal = $this->medsdiApi->agendarHoraMedica([
                'id_profesional' => $seleccion['id_profesional'],
                'id_lugar' => $seleccion['id_lugar'],
                'fecha' => $fechaHora->format('Y-m-d'),
                'hora' => $fechaHora->format('H:i:s'),
                'tipo_hora_medica' => (int) ($seleccion['id_especialidad'] ?? 0) === 2 ? 'D' : 'C',
                'tipo_agenda' => 1,
            ]);

            if (config('medsdi.booking_enabled') && ! $reservaReal['ok']) {
                throw new RuntimeException('Med-SDI rechazó la reserva: '.$reservaReal['mensaje']);
            }

            $valor = (float) ($cotizacion['valor'] ?? 0);
            $copago = (float) ($cotizacion['copago'] ?? 0);
            $codigo = 'BONO-EXT-'.now()->format('ymd').'-'.Str::upper(Str::random(7));

            $voucher = Voucher::create([
                'codigo' => $codigo, 'qr_token' => Str::random(80), 'qr_expira' => now()->addDays(30), 'qr_usado' => false,
                'cliente_id' => $user->id, 'cliente_rut' => Crypt::encryptString($rut), 'cliente_rut_hash' => hash('sha256', $rut),
                'cliente_nombre' => $persona->nombre_completo ?? trim(($pacienteMedsdi['nombres'] ?? '').' '.($pacienteMedsdi['apellido_uno'] ?? '').' '.($pacienteMedsdi['apellido_dos'] ?? '')), 'cliente_email' => $pacienteMedsdi['email'] ?? $user->email, 'cliente_telefono' => $pacienteMedsdi['telefono_uno'] ?? $user->telefono,
                'beneficiario_tipo' => 'titular', 'beneficiario_nombre' => $persona->nombre_completo ?? trim(($pacienteMedsdi['nombres'] ?? '').' '.($pacienteMedsdi['apellido_uno'] ?? '').' '.($pacienteMedsdi['apellido_dos'] ?? '')),
                'beneficiario_rut' => Crypt::encryptString($rut), 'beneficiario_rut_hash' => hash_hmac('sha256', $rut, (string) config('app.key')),
                'tipo_servicio' => $seleccion['prestacion_nombre'], 'valor' => $valor, 'valor_total' => $valor,
                'copago_usuario' => $copago, 'saldo_veterinario' => max($valor - $copago, 0), 'porcentaje_descuento' => 100,
                'estado' => 'pendiente_confirmacion', 'fecha_vencimiento' => now()->addDays(30), 'cliente_aceptado_en' => now(), 'otp_validado_at' => now(),
                'prestador_nombre' => $seleccion['nombre_profesional'], 'prestador_especialidad' => $seleccion['especialidad'],
                'prestador_direccion' => trim(($seleccion['lugar_nombre'] ?? '').' · '.($seleccion['direccion'] ?? '')),
            ]);
            $voucher->update(['qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))]);

            $horaRemotaId = (int) ($reservaReal['registros']['id'] ?? 0);
            if (config('medsdi.booking_enabled') && $horaRemotaId <= 0) {
                throw new RuntimeException('Med-SDI reservó la hora, pero no devolvió su identificador.');
            }
            $agenda = VoucherAgenda::create([
                'voucher_id' => $voucher->id, 'cliente_id' => $user->id,
                'fecha_hora_solicitada' => $fechaHora, 'fecha_hora_confirmada' => null,
                'estado' => 'hora_reservada',
                'observacion' => 'Reserva vía API Med-SDI (externa). Profesional #'.$seleccion['id_profesional'].' · '.($seleccion['lugar_nombre'] ?? ''),
                'medichile_hora_medica_id' => $horaRemotaId ?: null,
                'medichile_estado_id' => $horaRemotaId ? 1 : null,
                'medichile_sincronizado_at' => $horaRemotaId ? now() : null,
                'medichile_sync_error' => null,
            ]);
            $voucher->update(['agenda_id' => $agenda->id]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id, 'accion' => 'agenda_externa_medsdi_hora_reservada',
                'usuario_tipo' => 'cliente', 'usuario_id' => $user->id,
                'descripcion' => $reservaReal['ok']
                    ? 'Hora #'.$horaRemotaId.' reservada en Med-SDI; pendiente de confirmación y pago.'
                    : 'Bono demo generado localmente. '.$reservaReal['mensaje'],
                'ip' => $ip,
            ]);

            return $voucher->fresh(['agenda', 'pagos']);
        });
    }

    private function normalizarRut($rut): string { return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut)); }

    private function rutValido(string $rut): bool
    {
        if (! preg_match('/^(\d{7,8})([0-9K])$/', $rut, $p)) {
            return false;
        }
        $suma = 0;
        $multiplicador = 2;
        for ($i = strlen($p[1]) - 1; $i >= 0; $i--) {
            $suma += ((int) $p[1][$i]) * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        $resto = 11 - ($suma % 11);
        $dv = $resto === 11 ? '0' : ($resto === 10 ? 'K' : (string) $resto);

        return hash_equals($dv, $p[2]);
    }
}
