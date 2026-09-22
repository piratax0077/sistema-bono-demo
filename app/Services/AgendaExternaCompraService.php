<?php

namespace App\Services;

use App\Models\PersonaBusqueda;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseDependiente;
use App\Models\VoucherBaseUsuario;
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
            $esDependienteRemoto = ($pacienteMedsdi['tipo'] ?? null) === 'dependiente'
                || (bool) ($pacienteMedsdi['es_dependiente'] ?? false);
            $titularRemoto = is_array($pacienteMedsdi['titular'] ?? null) ? $pacienteMedsdi['titular'] : null;
            $rutTitularSolicitado = $this->normalizarRut($seleccion['titular_rut'] ?? '');
            if ($esDependienteRemoto && $rutTitularSolicitado !== '') {
                $titularCoincidente = collect($pacienteMedsdi['responsables'] ?? [])->first(function ($responsable) use ($rutTitularSolicitado) {
                    return $this->normalizarRut($responsable['rut'] ?? '') === $rutTitularSolicitado;
                });
                if (! is_array($titularCoincidente)) {
                    throw new RuntimeException('El titular seleccionado no figura como responsable vigente del dependiente en Med-SDI.');
                }
                $titularRemoto = $titularCoincidente;
            }
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

    public function comprarComoAsistente(?User $asistente, array $seleccion, string $rutIngresado, string $ip): Voucher
    {
        return DB::transaction(function () use ($asistente, $seleccion, $rutIngresado, $ip) {
            $rut = $this->normalizarRut($rutIngresado);
            if (! $this->rutValido($rut)) {
                throw new RuntimeException('RUT no válido. Revise el número y el dígito verificador.');
            }

            $perfilRemoto = $this->medsdiApi->pacientePorRutComoAsistente($rut);
            if (! ($perfilRemoto['ok'] ?? false) || ! is_array($perfilRemoto['paciente'] ?? null)) {
                throw new RuntimeException($perfilRemoto['mensaje'] ?? 'No fue posible validar al paciente en Med-SDI.');
            }
            $pacienteMedsdi = $perfilRemoto['paciente'];
            $esDependienteRemoto = ($pacienteMedsdi['tipo'] ?? null) === 'dependiente'
                || (bool) ($pacienteMedsdi['es_dependiente'] ?? false);
            $titularRemoto = is_array($pacienteMedsdi['titular'] ?? null) ? $pacienteMedsdi['titular'] : null;
            $rutTitularSolicitado = $this->normalizarRut($seleccion['titular_rut'] ?? '');
            $responsablesRemotos = collect(is_array($pacienteMedsdi['responsables'] ?? null) ? $pacienteMedsdi['responsables'] : []);
            if ($esDependienteRemoto && $responsablesRemotos->count() > 1 && $rutTitularSolicitado === '') {
                throw new RuntimeException('Seleccione al titular responsable vigente del dependiente.');
            }
            if ($esDependienteRemoto && $rutTitularSolicitado !== '') {
                $titularCoincidente = $responsablesRemotos->first(
                    fn ($responsable) => $this->normalizarRut($responsable['rut'] ?? '') === $rutTitularSolicitado
                );
                if (! is_array($titularCoincidente)) {
                    throw new RuntimeException('El titular seleccionado no figura como responsable vigente del dependiente en Med-SDI.');
                }
                $titularRemoto = $titularCoincidente;
            }
            $sha = hash('sha256', $rut);
            $hmac = hash_hmac('sha256', $rut, (string) config('app.key'));
            $dependiente = $this->vigente(VoucherBaseDependiente::with('usuario')
                ->where(fn ($query) => $query->whereIn('rut_hash', [$hmac, $sha])->orWhere('rut_sha256', $sha)))
                ->first();
            $titularBase = $dependiente?->usuario;
            if ($dependiente && (! $titularBase || ! $this->registroVigente($titularBase))) {
                throw new RuntimeException('El dependiente fue encontrado, pero su titular responsable no está vigente.');
            }
            if ($dependiente && $rutTitularSolicitado !== '') {
                $rutTitularBase = $this->normalizarRut($this->descifrar($titularBase?->rut_encrypted));
                if ($rutTitularBase === '' || ! hash_equals($rutTitularBase, $rutTitularSolicitado)) {
                    throw new RuntimeException('El titular seleccionado no coincide con el responsable vigente del dependiente en la base local.');
                }
            }
            if (! $dependiente) {
                $titularBase = $this->vigente(VoucherBaseUsuario::query()
                    ->where(fn ($query) => $query->whereIn('rut_hash', [$hmac, $sha])->orWhere('rut_sha256', $sha)))
                    ->first();
            }
            $esDependiente = (bool) $dependiente || $esDependienteRemoto;
            $titularRut = $dependiente
                ? $this->descifrar($titularBase?->rut_encrypted)
                : ($esDependienteRemoto ? ($titularRemoto['rut'] ?? null) : $rut);
            $titularRut = $this->normalizarRut($titularRut ?: $rut);
            $cliente = User::query()
                ->where('rol', 'cliente')
                ->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?", [$titularRut])
                ->first();
            // En la demo el usuario web es sólo la puerta de entrada y la
            // identidad clínica real proviene del token Med-SDI. Si el RUT
            // remoto no coincide con el usuario semilla, asociamos igualmente
            // el espejo local al perfil Paciente configurado para que ambos
            // escritorios operen sobre el mismo voucher.
            if (!$cliente && config('demo.enabled')) {
                $cliente = User::where('rol', 'cliente')
                    ->where('email', data_get(config('demo.users'), 'paciente.email'))
                    ->first();
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
            if (! ($cotizacionRemota['ok'] ?? false)) {
                throw new RuntimeException('No fue posible confirmar la cotización: '.$cotizacionRemota['mensaje']);
            }
            $cotizacion = $cotizacionRemota['cotizacion'];
            $reservaReal = $this->medsdiApi->agendarHoraMedicaComoAsistente([
                'id_profesional' => $seleccion['id_profesional'],
                'id_lugar' => $seleccion['id_lugar'],
                'fecha' => $fechaHora->format('Y-m-d'),
                'hora' => $fechaHora->format('H:i:s'),
                'tipo_hora_medica' => (int) ($seleccion['id_especialidad'] ?? 0) === 2 ? 'D' : 'C',
                'tipo_agenda' => 1,
            ], $rut);
            if (config('medsdi.booking_enabled') && ! ($reservaReal['ok'] ?? false)) {
                throw new RuntimeException('Med-SDI rechazó la reserva: '.$reservaReal['mensaje']);
            }

            $nombrePaciente = $dependiente?->nombre ?? $persona->nombre_completo ?? ($pacienteMedsdi['nombre_completo'] ?? trim(($pacienteMedsdi['nombres'] ?? '').' '.($pacienteMedsdi['apellido_uno'] ?? '').' '.($pacienteMedsdi['apellido_dos'] ?? '')));
            $nombreTitular = $titularBase?->nombre
                ?: ($esDependiente ? ($titularRemoto['nombre_completo'] ?? $cliente?->name) : $nombrePaciente);
            $titularOtros = is_array($titularBase?->otros) ? $titularBase->otros : [];
            $valor = (float) ($cotizacion['valor'] ?? 0);
            $copago = (float) ($cotizacion['copago'] ?? 0);
            $voucher = Voucher::create([
                'codigo' => 'BONO-EXT-'.now()->format('ymd').'-'.Str::upper(Str::random(7)),
                'qr_token' => Str::random(80), 'qr_expira' => now()->addDays(30), 'qr_usado' => false,
                'cliente_id' => $cliente?->id, 'cliente_rut' => Crypt::encryptString($titularRut), 'cliente_rut_hash' => hash('sha256', $titularRut),
                'cliente_nombre' => $nombreTitular ?: $nombrePaciente, 'cliente_email' => $cliente?->email ?? ($titularOtros['email'] ?? $titularRemoto['email'] ?? $pacienteMedsdi['email'] ?? null),
                'cliente_telefono' => $cliente?->telefono ?? ($titularOtros['telefono'] ?? $titularRemoto['telefono_uno'] ?? $pacienteMedsdi['telefono_uno'] ?? null),
                'beneficiario_tipo' => $esDependiente ? 'carga' : 'titular',
                'beneficiario_base_usuario_id' => $titularBase?->id,
                'beneficiario_dependiente_id' => $dependiente?->id,
                'beneficiario_nombre' => $nombrePaciente,
                'beneficiario_rut' => Crypt::encryptString($rut), 'beneficiario_rut_hash' => hash_hmac('sha256', $rut, (string) config('app.key')),
                'beneficiario_parentesco' => $dependiente?->parentesco ?: ($esDependienteRemoto ? ($pacienteMedsdi['parentesco'] ?? 'Carga') : 'Titular'),
                'beneficiario_direccion' => $dependiente?->direccion_encrypted ?: $titularBase?->direccion_encrypted,
                'beneficiario_fecha_nacimiento' => $dependiente?->fecha_nacimiento_encrypted ?: $titularBase?->fecha_nacimiento_encrypted,
                'tipo_servicio' => $seleccion['prestacion_nombre'], 'valor' => $valor, 'valor_total' => $valor,
                'copago_usuario' => $copago, 'saldo_veterinario' => max($valor - $copago, 0), 'porcentaje_descuento' => 100,
                'estado' => 'pendiente_confirmacion', 'fecha_vencimiento' => now()->addDays(30), 'cliente_aceptado_en' => now(), 'otp_validado_at' => now(),
                'prestador_nombre' => $seleccion['nombre_profesional'], 'prestador_especialidad' => $seleccion['especialidad'] ?? null,
                'prestador_direccion' => trim(($seleccion['lugar_nombre'] ?? '').' · '.($seleccion['direccion'] ?? '')),
            ]);
            $voucher->update(['qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))]);

            $horaRemotaId = (int) data_get($reservaReal, 'registros.id', 0);
            if (config('medsdi.booking_enabled') && $horaRemotaId <= 0) {
                throw new RuntimeException('Med-SDI reservó la hora, pero no devolvió su identificador.');
            }
            $agenda = VoucherAgenda::create([
                'voucher_id' => $voucher->id, 'cliente_id' => $cliente?->id,
                'fecha_hora_solicitada' => $fechaHora, 'fecha_hora_confirmada' => null, 'estado' => 'hora_reservada',
                'observacion' => ($asistente ? 'Reserva asistida' : 'Reserva desde tótem').' vía API Med-SDI. '.($asistente ? 'Operador local #'.$asistente->id.' · ' : '').'Profesional #'.$seleccion['id_profesional'],
                'medichile_hora_medica_id' => $horaRemotaId ?: null, 'medichile_estado_id' => $horaRemotaId ? 1 : null,
                'medichile_sincronizado_at' => $horaRemotaId ? now() : null, 'medichile_sync_error' => null,
            ]);
            $voucher->update(['agenda_id' => $agenda->id]);
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id, 'accion' => $asistente ? 'agenda_externa_asistente_hora_reservada' : 'agenda_externa_totem_hora_reservada',
                'usuario_tipo' => $asistente ? 'asistente' : 'totem', 'usuario_id' => $asistente?->id,
                'descripcion' => ($asistente ? 'La asistente' : 'El paciente desde el tótem').' reservó la hora Med-SDI #'.$horaRemotaId.' para '.$nombrePaciente.'.', 'ip' => $ip,
            ]);

            return $voucher->fresh(['agenda', 'pagos']);
        });
    }

    private function normalizarRut($rut): string { return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut)); }

    private function vigente($query)
    {
        return $query->whereIn('estado', ['activo', 'vigente'])
            ->where(fn ($builder) => $builder->whereNull('vigente_desde')->orWhere('vigente_desde', '<=', now()->toDateString()))
            ->where(fn ($builder) => $builder->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()->toDateString()));
    }

    private function registroVigente(VoucherBaseUsuario $usuario): bool
    {
        return in_array($usuario->estado, ['activo', 'vigente'], true)
            && (! $usuario->vigente_desde || $usuario->vigente_desde->lte(today()))
            && (! $usuario->vigente_hasta || $usuario->vigente_hasta->gte(today()));
    }

    private function descifrar(?string $valor): ?string
    {
        if (! filled($valor)) return null;
        try { return Crypt::decryptString($valor); } catch (\Throwable) { return $valor; }
    }

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
