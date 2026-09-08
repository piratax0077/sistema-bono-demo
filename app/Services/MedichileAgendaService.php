<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherAgenda;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MedichileAgendaService
{
    private const ESTADO_ESPERA = 4;

    public function marcarAtencionIniciada(Voucher $voucher, VoucherAgenda $agenda): array
    {
        return $this->actualizarEstadoClinico($voucher, $agenda, 5, 'Realizando');
    }

    public function marcarAtencionCerrada(Voucher $voucher, VoucherAgenda $agenda): array
    {
        return $this->actualizarEstadoClinico($voucher, $agenda, 6, 'Realizada');
    }

    public function verificarAtencionRealizada(Voucher $voucher, VoucherAgenda $agenda): array
    {
        if (! $agenda->medichile_hora_medica_id) {
            return ['ok' => false, 'detalle' => 'El bono no tiene una hora Medichile vinculada.'];
        }

        try {
            $hora = DB::connection('medichile')->table('horas_medicas')
                ->where('id', $agenda->medichile_hora_medica_id)
                ->first();
            $paciente = $this->buscarPorRut(
                'pacientes',
                $this->rutNormalizado($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible)
            );
            $profesional = $this->buscarPorRut(
                'profesionales',
                $this->rutNormalizado($voucher->prestador_rut)
            );

            $ok = $hora
                && $paciente
                && $profesional
                && (int) $hora->id_estado === 6
                && (int) $hora->id_paciente === (int) $paciente->id
                && (int) $hora->id_profesional === (int) $profesional->id;

            return [
                'ok' => (bool) $ok,
                'detalle' => $ok
                    ? 'Hora #'.$hora->id.' confirmada como Realizada y asociada al paciente y profesional en Medichile.'
                    : 'La hora Medichile no coincide con el paciente, profesional o estado Realizada.',
            ];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'detalle' => 'No fue posible contrastar la hora con Medichile: '.$exception->getMessage()];
        }
    }

    public function dejarPacienteEnEspera(Voucher $voucher, ?VoucherAgenda $agenda = null): array
    {
        $rutPaciente = $this->rutNormalizado($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible);
        $rutProfesional = $this->rutNormalizado($voucher->prestador_rut);

        if ($rutPaciente === '' || $rutProfesional === '') {
            throw new RuntimeException('El bono no tiene los RUT necesarios para vincular la agenda de Medichile.');
        }

        return DB::connection('medichile')->transaction(function () use ($voucher, $agenda, $rutPaciente, $rutProfesional) {
            $paciente = $this->buscarPorRut('pacientes', $rutPaciente);
            $profesional = $this->buscarPorRut('profesionales', $rutProfesional);

            if (! $paciente) {
                throw new RuntimeException('El paciente del bono no existe en Medichile.');
            }

            if (! $profesional) {
                throw new RuntimeException('El profesional del bono no existe en Medichile.');
            }

            $hora = null;
            if ($agenda && $agenda->medichile_hora_medica_id) {
                $hora = DB::connection('medichile')->table('horas_medicas')
                    ->where('id', $agenda->medichile_hora_medica_id)
                    ->lockForUpdate()
                    ->first();

                if ($hora && ((int) $hora->id_paciente !== (int) $paciente->id
                    || (int) $hora->id_profesional !== (int) $profesional->id)) {
                    throw new RuntimeException('La hora vinculada no corresponde al paciente y profesional del bono.');
                }
            }

            if (! $hora) {
                $hora = DB::connection('medichile')->table('horas_medicas')
                    ->where('descripcion', 'SDI-'.$voucher->codigo)
                    ->where('id_paciente', $paciente->id)
                    ->where('id_profesional', $profesional->id)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $hora) {
                $candidatas = DB::connection('medichile')->table('horas_medicas')
                    ->where('id_paciente', $paciente->id)
                    ->where('id_profesional', $profesional->id)
                    ->whereDate('fecha_consulta', now()->toDateString())
                    ->orderBy('hora_inicio')
                    ->lockForUpdate()
                    ->get();

                if ($candidatas->count() === 1) {
                    $hora = $candidatas->first();
                } elseif ($candidatas->count() > 1) {
                    throw new RuntimeException('Hay más de una hora de hoy para este paciente y profesional; vincule la hora exacta antes de recibirlo.');
                }
            }

            if (! $hora) {
                throw new RuntimeException('No existe una hora médica de hoy asociada al paciente y profesional en Medichile.');
            }

            $marca = 'Recepción SDI bono '.$voucher->codigo;
            $observaciones = trim((string) ($hora->observaciones ?? ''));
            if (! str_contains($observaciones, $marca)) {
                $observaciones = trim($observaciones === '' ? $marca : $observaciones.' | '.$marca);
            }

            DB::connection('medichile')->table('horas_medicas')
                ->where('id', $hora->id)
                ->update([
                    'id_estado' => self::ESTADO_ESPERA,
                    'observaciones' => mb_substr($observaciones, 0, 255),
                    'updated_at' => now(),
                ]);

            return [
                'hora_medica_id' => (int) $hora->id,
                'estado_id' => self::ESTADO_ESPERA,
                'estado_nombre' => 'Espera',
                'sincronizado_at' => now(),
            ];
        });
    }

    private function buscarPorRut(string $tabla, string $rut)
    {
        return DB::connection('medichile')->table($tabla)
            ->whereRaw("UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?", [$rut])
            ->first();
    }

    private function actualizarEstadoClinico(Voucher $voucher, VoucherAgenda $agenda, int $estadoId, string $estadoNombre): array
    {
        if (! $agenda->medichile_hora_medica_id) {
            throw new RuntimeException('La atención no tiene una hora Medichile vinculada.');
        }

        return DB::connection('medichile')->transaction(function () use ($voucher, $agenda, $estadoId, $estadoNombre) {
            $hora = DB::connection('medichile')->table('horas_medicas')
                ->where('id', $agenda->medichile_hora_medica_id)
                ->lockForUpdate()
                ->first();

            if (! $hora) {
                throw new RuntimeException('La hora vinculada ya no existe en Medichile.');
            }

            $marca = 'SDI bono '.$voucher->codigo.' - '.$estadoNombre;
            $observaciones = trim((string) ($hora->observaciones ?? ''));
            if (! str_contains($observaciones, $marca)) {
                $observaciones = trim($observaciones === '' ? $marca : $observaciones.' | '.$marca);
            }

            $cambios = [
                'id_estado' => $estadoId,
                'observaciones' => mb_substr($observaciones, 0, 255),
                'updated_at' => now(),
            ];
            if ($estadoId === 6) {
                $cambios['fecha_realizacion_consulta'] = now();
            }

            DB::connection('medichile')->table('horas_medicas')
                ->where('id', $hora->id)
                ->update($cambios);

            return [
                'hora_medica_id' => (int) $hora->id,
                'estado_id' => $estadoId,
                'estado_nombre' => $estadoNombre,
                'sincronizado_at' => now(),
            ];
        });
    }

    private function rutNormalizado($rut): string
    {
        return strtoupper((string) preg_replace('/[^0-9kK]/', '', (string) $rut));
    }
}
