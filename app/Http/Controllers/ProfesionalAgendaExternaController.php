<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Services\MedsdiAgendaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfesionalAgendaExternaController extends Controller
{
    /**
     * Mapa id_estado real de Med-SDI -> estado local de voucher_agendas,
     * igual al usado por ClienteAgendaExternaController::sincronizarHora.
     */
    private const ESTADO_LOCAL = [
        1 => 'hora_reservada',
        2 => 'hora_confirmada',
        3 => 'hora_rechazada',
        4 => 'paciente_en_espera',
        5 => 'hora_confirmada',
        6 => 'atencion_realizada',
        7 => 'no_asiste',
    ];

    /**
     * Trae en vivo la agenda del profesional en Med-SDI y actualiza el
     * id_estado real sobre las agendas locales ya vinculadas, para no
     * depender de que el paciente sincronice antes desde su bono.
     */
    public function sincronizarAgenda(MedsdiAgendaApiService $api): void
    {
        $resultado = $api->agendaProfesional();
        if (! $resultado['ok']) {
            return;
        }

        $horasPorId = collect($resultado['registros'])->keyBy('id');
        if ($horasPorId->isEmpty()) {
            return;
        }

        $agendas = VoucherAgenda::whereIn('medichile_hora_medica_id', $horasPorId->keys())->get();
        foreach ($agendas as $agenda) {
            $idEstado = (int) ($horasPorId->get($agenda->medichile_hora_medica_id)['id_estado'] ?? 0);
            if ($idEstado <= 0 || (int) $agenda->medichile_estado_id === $idEstado) {
                continue;
            }

            $agenda->update([
                'estado' => self::ESTADO_LOCAL[$idEstado] ?? $agenda->estado,
                'medichile_estado_id' => $idEstado,
                'medichile_sincronizado_at' => now(),
                'medichile_sync_error' => null,
            ]);
        }
    }

    /**
     * Bonos externos Med-SDI (todos del profesional demo Jaime Kriman Astorga)
     * cuyo paciente ya está confirmado en sala de espera (medichile_estado_id=4),
     * como Voucher listos para unirse a la tabla local "Pacientes en espera".
     */
    public function pacientesEnEsperaExternos()
    {
        return Voucher::with('agenda')
            ->whereNotNull('prestador_nombre')
            ->whereHas('agenda', fn ($q) => $q->where('medichile_estado_id', 4))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Bonos externos Med-SDI cuya hora ya está siendo atendida
     * (medichile_estado_id=5), sincronizada localmente vía el botón
     * "Actualizar estado Med-SDI" del paciente.
     */
    public function misBonos(): array
    {
        $agendas = VoucherAgenda::with('voucher')
            ->whereNotNull('medichile_hora_medica_id')
            ->where('medichile_estado_id', 5)
            ->whereHas('voucher', fn ($q) => $q->whereNotNull('prestador_nombre'))
            ->orderByDesc('id')
            ->get();

        $registros = $agendas->map(function ($agenda) {
            return [
                'id_hora_medica' => $agenda->medichile_hora_medica_id,
                'estado_medsdi' => 'Realizando atención',
                'puede_finalizar' => true,
                'agenda' => $agenda,
                'voucher' => $agenda->voucher,
            ];
        })->values();

        return ['disponible' => true, 'mensaje' => 'ok', 'registros' => $registros];
    }

    public function iniciarAtencion(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        $agenda = $voucher->agenda;
        if (! $agenda || ! $agenda->medichile_hora_medica_id) {
            return back()->with('error', 'Este bono no tiene una hora Med-SDI vinculada.');
        }

        $resultado = $api->iniciarAtencionHoraMedicaProfesional((int) $agenda->medichile_hora_medica_id);
        if (! $resultado['ok']) {
            return back()->with('error', 'Med-SDI: '.($resultado['mensaje'] ?? 'no fue posible iniciar la atención.'));
        }

        DB::transaction(function () use ($request, $voucher, $agenda) {
            $agenda->update([
                'estado' => self::ESTADO_LOCAL[5],
                'medichile_estado_id' => 5,
                'medichile_sincronizado_at' => now(),
                'medichile_sync_error' => null,
            ]);
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'agenda_externa_atencion_iniciada_profesional',
                'usuario_tipo' => 'profesional',
                'usuario_id' => $request->user()->id,
                'descripcion' => 'Hora Med-SDI #'.$agenda->medichile_hora_medica_id.' pasó a Realizando.',
                'ip' => $request->ip(),
            ]);
        });

        return back()->with('ok', 'Atención iniciada en Med-SDI.');
    }

    public function finalizarHora(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        $agenda = $voucher->agenda;
        if (! $agenda || ! $agenda->medichile_hora_medica_id) {
            return back()->with('error', 'Este bono no tiene una hora Med-SDI vinculada.');
        }

        $resultado = $api->finalizarHoraMedicaProfesional((int) $agenda->medichile_hora_medica_id);
        if (! $resultado['ok']) {
            return back()->with('error', 'Med-SDI: '.($resultado['mensaje'] ?? 'no fue posible finalizar la hora.'));
        }

        DB::transaction(function () use ($request, $voucher, $agenda) {
            $agenda->update([
                'estado' => 'atencion_realizada',
                'medichile_estado_id' => 6,
                'medichile_sincronizado_at' => now(),
                'medichile_sync_error' => null,
            ]);
            // Sin diagnóstico local (la ficha clínica vive en Med-SDI): se
            // valida automáticamente igual que finalizarAtencion() del flujo
            // de convenio local, para habilitar el bono en "Atenciones cerradas".
            $voucher->update([
                'estado' => 'validado_atencion',
                'atencion_cerrada_at' => $voucher->atencion_cerrada_at ?: now(),
                'validado_at' => now(),
                'estado_validacion' => 'validada_automaticamente',
                'riesgo_validacion' => 'bajo',
            ]);
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'agenda_externa_hora_finalizada_profesional',
                'usuario_tipo' => 'profesional',
                'usuario_id' => $request->user()->id,
                'descripcion' => 'Hora Med-SDI #'.$agenda->medichile_hora_medica_id.' finalizada por el profesional.',
                'ip' => $request->ip(),
            ]);
        });

        return back()->with('ok', 'Hora médica finalizada en Med-SDI.');
    }
}
