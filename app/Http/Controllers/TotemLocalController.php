<?php

namespace App\Http\Controllers;

use App\Models\Totem;
use App\Models\TotemLog;
use App\Models\AgendaOnlineHorario;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Services\MedichileAgendaService;
use App\Services\MedsdiAgendaApiService;
use App\Services\AgendaExternaCompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TotemLocalController extends Controller
{
    public function index(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        abort_unless(config('demo.enabled'), 404);
        $totem = $this->totemActivo($request);
        $bonos = collect();
        // El tótem siempre comienza anónimo. El beneficiario se valida por RUT
        // dentro del modal y nunca se hereda desde una sesión de paciente.
        $perfilRemotoMedsdi = null;
        $pacienteMedsdi = null;
        $horariosOnline = AgendaOnlineHorario::with(['profesional', 'servicio'])
            ->where('estado', 'disponible')
            ->where('fecha_hora', '>', now())
            ->orderBy('fecha_hora')
            ->take(30)
            ->get();
        $agendaOnlineResultado = $request->session()->get('agenda_online_voucher_id')
            ? Voucher::with(['agenda', 'profesional'])->find($request->session()->get('agenda_online_voucher_id'))
            : null;
        $ids = array_values(array_filter(array_map('intval', (array) $request->session()->get('totem_checkin_voucher_ids', []))));
        $identificacionAutomatica = false;

        if ($request->query('tab') === 'autoatencion') {
            // Solo se conserva el resultado de una búsqueda manual (QR/RUT) en el
            // request inmediatamente siguiente a esa búsqueda (marcado por las
            // acciones abajo); cualquier otra entrada a esta vista (refresco,
            // volver a la pestaña, etc.) parte con el buscador limpio.
            if (! $request->session()->pull('totem_mostrar_busqueda_reciente', false)) {
                $request->session()->forget(['totem_checkin_voucher_ids', 'totem_checkin_rut_hash']);
                $ids = [];
            }

            $idsAutomaticos = collect($ids);

            if ($agendaOnlineResultado) {
                $idsAutomaticos->push($agendaOnlineResultado->id);
            }

            if ($request->user()?->rol === 'cliente') {
                $idsAutomaticos = $idsAutomaticos->merge(
                    Voucher::query()
                        ->where('cliente_id', $request->user()->id)
                        ->whereNotIn('estado', ['cobrado', 'usado', 'invalidado_cliente'])
                        ->where('qr_usado', false)
                        ->whereNotNull('profesional_id')
                        ->whereHas('agenda', function ($query) {
                            $query->where('estado', 'hora_confirmada');
                        })
                        ->latest('id')
                        ->limit(10)
                        ->pluck('id')
                );
            }

            $ids = $idsAutomaticos->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
            if ($ids !== []) {
                $request->session()->put('totem_checkin_voucher_ids', $ids);
                $identificacionAutomatica = true;
            }
        }

        if ($ids !== []) {
            $bonos = Voucher::with(['agenda', 'profesional', 'servicio'])
                ->whereIn('id', $ids)
                ->orderByDesc('id')
                ->get();
        }

        return view('totem.local', compact(
            'totem',
            'bonos',
            'horariosOnline',
            'agendaOnlineResultado',
            'pacienteMedsdi',
            'perfilRemotoMedsdi',
            'identificacionAutomatica'
        ));
    }

    public function paciente(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        abort_unless(config('demo.enabled'), 404);
        if (! $this->totemActivo($request)) {
            return response()->json(['ok' => false, 'mensaje' => 'El tótem no está activo o provisionado.'], 423);
        }
        $data = $request->validate(['rut' => ['required', 'string', 'max:30']]);
        $rut = $this->normalizarRut($data['rut']);

        if (! $this->rutValido($rut)) {
            return response()->json(['ok' => false, 'mensaje' => 'Ingrese un RUT chileno válido.'], 422);
        }

        return response()->json($medsdiApi->pacientePorRutComoAsistente($rut));
    }

    public function agendar(Request $request, AgendaExternaCompraService $service)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);
        if (! $this->totemActivo($request)) {
            return response()->json(['ok' => false, 'mensaje' => 'El tótem no está activo o provisionado.'], 423);
        }
        $data = $request->validate([
            'id_profesional' => ['required', 'integer'], 'nombre_profesional' => ['required', 'string', 'max:190'],
            'especialidad' => ['nullable', 'string', 'max:190'], 'id_especialidad' => ['nullable', 'integer'],
            'id_lugar' => ['required', 'integer'], 'lugar_nombre' => ['nullable', 'string', 'max:190'],
            'direccion' => ['nullable', 'string', 'max:255'], 'fecha_hora' => ['required', 'date'],
            'rut' => ['required', 'string', 'max:30'], 'titular_rut' => ['nullable', 'string', 'max:30'],
            'id_prestacion' => ['required', 'integer'], 'origen_prestacion' => ['required', 'in:prestacion_fonasa_bono'],
            'prestacion_codigo' => ['required', 'string', 'max:40'], 'prestacion_nombre' => ['required', 'string', 'max:255'],
        ]);

        try {
            $voucher = $service->comprarComoAsistente(null, $data, $data['rut'], $request->ip());
            $request->session()->put('agenda_online_voucher_id', $voucher->id);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Hora reservada correctamente.',
                'voucher' => $voucher->codigo,
                'redirect' => route('paciente.totem', ['tab' => 'reserva']),
            ]);
        } catch (Throwable $exception) {
            return response()->json(['ok' => false, 'mensaje' => $exception->getMessage()], 422);
        }
    }

    public function buscarHora(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        abort_unless(config('demo.enabled'), 404);
        $data = $request->validate(['rut' => ['required', 'string', 'max:20']]);
        $rut = $this->normalizarRut($data['rut']);

        if (! $this->rutValido($rut)) {
            return back()->withInput()->withErrors(['rut' => 'Ingrese un RUT chileno válido.']);
        }

        $resultadoMedsdi = $medsdiApi->horasVigentesPorRut($rut);
        if (! $resultadoMedsdi['ok']) {
            $request->session()->forget(['totem_checkin_voucher_ids', 'totem_checkin_rut_hash']);

            return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                ->withInput()
                ->with('error', $resultadoMedsdi['mensaje']);
        }

        $idsHorasMedsdi = collect($resultadoMedsdi['registros'] ?? [])
            ->map(fn ($hora) => (int) ($hora['id_hora_medica'] ?? $hora['id'] ?? 0))
            ->filter()
            ->unique()
            ->values();

        $bonos = Voucher::with(['agenda', 'profesional', 'servicio'])
            ->whereNotIn('estado', ['cobrado', 'usado', 'invalidado_cliente'])
            ->where('qr_usado', false)
            ->whereHas('agenda', function ($query) use ($idsHorasMedsdi) {
                $query->whereIn('medichile_hora_medica_id', $idsHorasMedsdi->all())
                    ->where('estado', '!=', 'paciente_en_espera');
            })
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->values();

        if ($bonos->isEmpty()) {
            $request->session()->forget('totem_checkin_voucher_ids');
            return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                ->with('error', 'Med-SDI encontró una hora vigente, pero no existe un bono local vinculado a esa hora.');
        }

        $sha = hash('sha256', $rut);
        $request->session()->put('totem_checkin_voucher_ids', $bonos->pluck('id')->all());
        $request->session()->put('totem_checkin_rut_hash', $sha);
        $request->session()->put('totem_mostrar_busqueda_reciente', true);

        return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
            ->with('ok', 'Paciente validado. Seleccione la hora y confirme su llegada.');
    }

    public function anexarQr(Request $request)
    {
        abort_unless(config('demo.enabled'), 404);
        $data = $request->validate(['qr' => ['required', 'string', 'max:500']]);
        $token = $this->extraerTokenQr($data['qr']);
        $voucher = Voucher::with(['agenda', 'profesional', 'servicio'])
            ->where('qr_token', $token)->first();

        if (! $voucher || ! hash_equals((string) $voucher->qr_token, $token)) {
            return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                ->withErrors(['qr' => 'El QR no corresponde a un bono Medichile válido.']);
        }
        if ($voucher->qr_usado
            || ($voucher->qr_expira && now()->greaterThan(\Illuminate\Support\Carbon::parse($voucher->qr_expira)))
            || in_array($voucher->estado, ['cobrado', 'usado', 'invalidado_cliente'], true)) {
            return redirect()->route('totem.local', ['tab' => 'autoatencion'])
                ->withErrors(['qr' => 'El QR está vencido o el bono ya fue utilizado.']);
        }
        if (! $voucher->agenda) {
            return redirect()->route('totem.local', ['tab' => 'autoatencion'])
                ->withErrors(['qr' => 'El bono no tiene una hora médica asociada.']);
        }

        $request->session()->put('totem_checkin_voucher_ids', [$voucher->id]);
        $request->session()->forget('totem_checkin_rut_hash');
        $request->session()->put('totem_mostrar_busqueda_reciente', true);
        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'qr_anexado_en_totem',
            'usuario_tipo' => 'totem',
            'usuario_id' => auth()->id(),
            'descripcion' => 'QR leído y asociado a la autoatención antes de confirmar la llegada.',
            'ip' => $request->ip(),
        ]);

        return redirect()->route('totem.local', ['tab' => 'autoatencion'])
            ->with('ok', 'QR reconocido. Revise la hora y confirme su llegada.');
    }

    public function confirmarLlegada(Request $request, Voucher $voucher, MedichileAgendaService $medichileAgenda, MedsdiAgendaApiService $medsdiApi)
    {
        abort_unless(config('demo.enabled'), 404);
        $permitidos = array_map('intval', (array) $request->session()->get('totem_checkin_voucher_ids', []));
        if (! in_array((int) $voucher->id, $permitidos, true)) {
            return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                ->with('error', 'Vuelva a validar el RUT antes de confirmar la llegada.');
        }
        $request->session()->put('totem_mostrar_busqueda_reciente', true);

        try {
            return DB::transaction(function () use ($request, $voucher, $medichileAgenda, $medsdiApi) {
                $totem = Totem::where('activo', true)->orderBy('id')->lockForUpdate()->first();
                if (! $totem) {
                    return back()->with('error', 'El tótem está bloqueado o no está provisionado.');
                }

                $voucher = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                if ($voucher->qr_usado || in_array($voucher->estado, ['cobrado', 'usado', 'invalidado_cliente'], true)) {
                    return back()->with('error', 'El bono ya no está vigente para registrar la llegada.');
                }

                $agenda = VoucherAgenda::where('voucher_id', $voucher->id)->lockForUpdate()->first();
                if (! $agenda) {
                    return back()->with('error', 'El bono no tiene una hora médica asociada.');
                }
                if ($agenda->estado === 'paciente_en_espera' && (int) $agenda->medichile_estado_id === 4) {
                    return back()->with('ok', 'Su llegada ya estaba confirmada. El profesional ya puede verla en sala de espera.');
                }

                $sync = null;
                $syncError = null;

                // Bonos del flujo "Reservar hora (Med-SDI)" no tienen convenio local
                // (profesional_id null) y ya traen su propia hora real vinculada; en
                // ese caso se marca la llegada directo en medsdi.test en vez de usar
                // el flujo legacy de convenios locales (que busca por RUT en la base
                // espejo local y no aplica a estos bonos).
                if (! $voucher->profesional_id && $agenda->medichile_hora_medica_id) {
                    $rutPaciente = (string) ($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible);
                    $resultado = $medsdiApi->confirmarLlegadaSalaEspera(
                        (int) $agenda->medichile_hora_medica_id,
                        $rutPaciente
                    );
                    if (! $resultado['ok']) {
                        throw new \RuntimeException($resultado['mensaje']);
                    }
                    $sync = [
                        'hora_medica_id' => (int) $agenda->medichile_hora_medica_id,
                        'estado_id' => (int) ($resultado['registros']['id_estado'] ?? $agenda->medichile_estado_id ?? 4),
                        'estado_nombre' => 'Espera',
                        'sincronizado_at' => $resultado['ok'] ? now() : null,
                    ];
                    $syncError = $resultado['ok'] ? null : $resultado['mensaje'];
                } else {
                    $sync = $medichileAgenda->dejarPacienteEnEspera($voucher, $agenda);
                }

                $llegada = now();
                $agenda->update([
                    'estado' => 'paciente_en_espera',
                    'observacion' => 'Llegada confirmada por el paciente desde tótem '.$totem->codigo.'.',
                    'medichile_hora_medica_id' => $sync['hora_medica_id'],
                    'medichile_estado_id' => $sync['estado_id'],
                    'medichile_sincronizado_at' => $sync['sincronizado_at'],
                    'medichile_sync_error' => $syncError,
                ]);

                $delivery = VoucherDeliveryRequest::where('voucher_id', $voucher->id)
                    ->where('canal', 'assistant_totem_reception')->latest('id')->first();
                if ($delivery) {
                    $metadata = $delivery->metadata ?: [];
                    $metadata['estado_paciente'] = 'esperando_atencion';
                    $metadata['canal_recepcion_qr'] = 'autoatencion_totem';
                    $metadata['medichile_hora_medica_id'] = $sync['hora_medica_id'];
                    $metadata['medichile_estado'] = $sync['estado_nombre'];
                    $metadata['recibido_en'] = $llegada->toIso8601String();
                    $metadata['totem_codigo'] = $totem->codigo;
                    $delivery->update(['estado' => 'received', 'metadata' => $metadata]);
                }

                $voucher->update(['agenda_id' => $agenda->id, 'estado' => 'asignado']);
                $totem->update(['ultimo_ping' => $llegada, 'ultimo_acceso' => $llegada, 'estado_operacional' => 'ok']);
                TotemLog::create([
                    'totem_id' => $totem->id,
                    'evento' => 'paciente_confirma_llegada',
                    'detalle' => 'Bono '.$voucher->codigo.' · hora Medichile #'.$sync['hora_medica_id'].' en Espera.',
                    'ip' => $request->ip(),
                ]);
                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'autoatencion_totem_paciente_en_espera',
                    'usuario_tipo' => 'totem',
                    'usuario_id' => auth()->id(),
                    'descripcion' => 'Paciente confirmó llegada en '.$totem->codigo.'. Hora Medichile #'.$sync['hora_medica_id'].' actualizada a Espera. Profesional '.$voucher->prestador_nombre.'.',
                    'ip' => $request->ip(),
                ]);

                return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                    ->with('checkin_ok', 'Llegada confirmada. Su hora está en ESPERA y el profesional fue notificado en su escritorio.');
            });
        } catch (Throwable $exception) {
            Log::error('Error de autoatención en tótem local.', ['voucher_id' => $voucher->id, 'error' => $exception->getMessage()]);
            return redirect()->route($this->rutaAutoatencion($request), ['tab' => 'autoatencion'])
                ->with('error', 'No se cambió la hora: '.$exception->getMessage());
        }
    }

    private function totemActivo(Request $request): ?Totem
    {
        $totem = Totem::where('activo', true)->orderBy('id')->first();
        if ($totem) {
            $totem->update(['ultimo_ping' => now(), 'ultimo_acceso' => now(), 'estado_operacional' => 'ok', 'ultima_alerta_mensaje' => null]);
        }
        return $totem?->fresh();
    }

    private function rutaAutoatencion(Request $request): string
    {
        return $request->input('origen') === 'paciente-totem'
            ? 'paciente.totem'
            : 'totem.local';
    }

    private function normalizarRut(string $rut): string
    {
        return strtoupper((string) preg_replace('/[^0-9K]/i', '', $rut));
    }

    private function extraerTokenQr(string $valor): string
    {
        $valor = trim($valor);
        $path = parse_url($valor, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            $segmentos = array_values(array_filter(explode('/', trim($path, '/'))));
            $segmento = (string) end($segmentos);
            if (in_array($segmento, ['usar', 'lector-demo', 'whatsapp-demo'], true) && count($segmentos) > 1) {
                $segmento = $segmentos[count($segmentos) - 2];
            }
            if ($segmento !== '' && $segmento !== '.') {
                return $segmento;
            }
        }

        return $valor;
    }

    private function rutValido(string $rut): bool
    {
        if (! preg_match('/^(\d{7,8})([0-9K])$/', $rut, $partes)) return false;
        $suma = 0; $multiplicador = 2;
        for ($i = strlen($partes[1]) - 1; $i >= 0; $i--) {
            $suma += ((int) $partes[1][$i]) * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        $resultado = 11 - ($suma % 11);
        $dv = $resultado === 11 ? '0' : ($resultado === 10 ? 'K' : (string) $resultado);
        return hash_equals($dv, $partes[2]);
    }
}
