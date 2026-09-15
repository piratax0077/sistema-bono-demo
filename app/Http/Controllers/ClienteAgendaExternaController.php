<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherPago;
use App\Services\AgendaExternaCompraService;
use App\Services\MedsdiAgendaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClienteAgendaExternaController extends Controller
{
    public function regiones(MedsdiAgendaApiService $api)
    {
        return response()->json($api->regiones());
    }

    public function ciudades(Request $request, MedsdiAgendaApiService $api)
    {
        $request->validate(['id_region' => ['required', 'integer']]);

        return response()->json($api->ciudades((int) $request->id_region));
    }

    public function especialidades(MedsdiAgendaApiService $api)
    {
        return response()->json($api->especialidades());
    }

    public function prestaciones(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate(['buscar' => ['required', 'string', 'min:2', 'max:120']]);

        return response()->json($api->prestaciones(trim($data['buscar'])));
    }

    public function cotizar(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate([
            'id_profesional' => ['required', 'integer'],
            'id_lugar_atencion' => ['required', 'integer'],
            'id_prestacion' => ['required', 'integer'],
            'origen_prestacion' => ['required', 'in:prestacion_fonasa_bono'],
        ]);

        return response()->json($api->cotizar($data));
    }

    public function tipoEspecialidades(Request $request, MedsdiAgendaApiService $api)
    {
        $request->validate(['id_especialidad' => ['required', 'integer']]);

        return response()->json($api->tipoEspecialidades((int) $request->id_especialidad));
    }

    public function subTipoEspecialidades(Request $request, MedsdiAgendaApiService $api)
    {
        $request->validate(['id_tipo_especialidad' => ['required', 'integer']]);

        return response()->json($api->subTipoEspecialidades((int) $request->id_tipo_especialidad));
    }

    public function profesionales(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate([
            'id_region' => ['nullable', 'integer'],
            'id_ciudad' => ['nullable', 'integer'],
            'id_lugar_atencion' => ['nullable', 'integer'],
            'id_especialidad' => ['nullable', 'integer'],
            'id_tipo_especialidad' => ['nullable', 'integer'],
            'id_sub_tipo_especialidad' => ['nullable', 'integer'],
            'nombre_profesional' => ['nullable', 'string', 'max:150'],
            'incluir_todos_lugares' => ['nullable', 'boolean'],
        ]);

        return response()->json($api->buscarProfesionales(array_filter($data, fn ($v) => $v !== null && $v !== '')));
    }

    public function diasLaborales(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate([
            'id_profesional' => ['required', 'integer'],
            'id_especialidad' => ['nullable', 'integer'],
            'id_lugar' => ['required', 'integer'],
        ]);

        return response()->json($api->diasLaborales((int) $data['id_profesional'], (int) $data['id_lugar']));
    }

    public function horasDisponibles(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate([
            'id_profesional' => ['required', 'integer'],
            'id_lugar' => ['required', 'integer'],
            'fecha' => ['required', 'date'],
        ]);

        return response()->json($api->horasDisponibles((int) $data['id_profesional'], (int) $data['id_lugar'], $data['fecha']));
    }

    public function agendar(Request $request, AgendaExternaCompraService $service)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);

        $data = $request->validate([
            'id_profesional' => ['required', 'integer'],
            'nombre_profesional' => ['required', 'string', 'max:190'],
            'especialidad' => ['nullable', 'string', 'max:190'],
            'id_lugar' => ['required', 'integer'],
            'lugar_nombre' => ['nullable', 'string', 'max:190'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_hora' => ['required', 'date'],
            'rut' => ['required', 'string', 'max:30'],
            'id_prestacion' => ['required', 'integer'],
            'origen_prestacion' => ['required', 'in:prestacion_fonasa_bono'],
            'prestacion_codigo' => ['required', 'string', 'max:40'],
            'prestacion_nombre' => ['required', 'string', 'max:255'],
        ]);

        try {
            $voucher = $service->comprar($request->user(), $data, $data['rut'], $request->ip());

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'mensaje' => 'Med-SDI confirmó que la hora fue reservada correctamente.',
                    'voucher' => [
                        'id' => $voucher->id,
                        'codigo' => $voucher->codigo,
                        'servicio' => $voucher->tipo_servicio,
                        'estado' => $voucher->estado,
                        'hora_medsdi_id' => optional($voucher->agenda)->medichile_hora_medica_id,
                    ],
                ]);
            }

            return redirect()->route('cliente.dashboard')
                ->with('ok', 'Hora reservada en Med-SDI. Ahora debe confirmarla y luego simular el pago.')
                ->with('agenda_online_voucher_id', $voucher->id);
        } catch (Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'mensaje' => $exception->getMessage()], 422);
            }

            return redirect()->route('cliente.dashboard')->withInput()->with('error', $exception->getMessage());
        }
    }

    public function confirmarHora(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        abort_unless((int) $voucher->cliente_id === (int) $request->user()->id, 403);
        $agenda = $voucher->agenda;
        if (! $agenda || ! $agenda->medichile_hora_medica_id) {
            return back()->with('error', 'El bono no tiene una hora Med-SDI vinculada.');
        }
        if ($voucher->estado !== 'pendiente_confirmacion' || $agenda->estado !== 'hora_reservada') {
            return back()->with('error', 'La hora no se encuentra pendiente de confirmación.');
        }

        $resultado = $api->confirmarHoraMedica((int) $agenda->medichile_hora_medica_id);
        if (! $resultado['ok']) {
            return back()->with('error', 'Med-SDI no pudo confirmar la hora: '.$resultado['mensaje']);
        }

        DB::transaction(function () use ($request, $voucher, $agenda, $resultado) {
            $agenda->update([
                'estado' => 'hora_confirmada',
                'fecha_hora_confirmada' => $agenda->fecha_hora_solicitada,
                'medichile_estado_id' => (int) ($resultado['registros']['id_estado'] ?? 2),
                'medichile_sincronizado_at' => now(),
                'medichile_sync_error' => null,
            ]);
            $voucher->update(['estado' => 'pendiente_pago']);
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'agenda_externa_hora_confirmada',
                'usuario_tipo' => 'cliente',
                'usuario_id' => $request->user()->id,
                'descripcion' => 'Hora Med-SDI #'.$agenda->medichile_hora_medica_id.' confirmada; pendiente de pago.',
                'ip' => $request->ip(),
            ]);
        });

        return back()->with('ok', 'Hora confirmada. El copago quedó pendiente de pago.');
    }

    /**
     * Consulta en Med-SDI el estado real de la hora vinculada (sin mutarla) y
     * actualiza la agenda/voucher local para reflejarlo. Es lo que permite
     * saber, antes de cobrar, si la hora sigue reservada, ya fue confirmada,
     * quedó rechazada o el paciente no asistió.
     */
    public function sincronizarHora(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        abort_unless((int) $voucher->cliente_id === (int) $request->user()->id, 403);

        $agenda = $voucher->agenda;
        if (! $agenda || ! $agenda->medichile_hora_medica_id) {
            return $this->respuestaSincronizacion($request, $voucher, false, 'Este bono no tiene una hora Med-SDI vinculada para sincronizar.');
        }

        $perfilRemoto = $api->pacienteAutenticado();
        $idPaciente = (int) data_get($perfilRemoto, 'paciente.id', 0);
        if (! $perfilRemoto['ok'] || $idPaciente <= 0) {
            return $this->respuestaSincronizacion($request, $voucher, false, 'No fue posible identificar al paciente en Med-SDI para sincronizar la hora.');
        }

        $estadoRemoto = $api->estadoHoraMedica((int) $agenda->medichile_hora_medica_id, $idPaciente);

        if (! $estadoRemoto['ok']) {
            $agenda->update(['medichile_sync_error' => $estadoRemoto['mensaje'] ?? 'No fue posible sincronizar la hora.']);

            return $this->respuestaSincronizacion($request, $voucher, false, 'Med-SDI: '.($estadoRemoto['mensaje'] ?? 'no fue posible sincronizar la hora.'));
        }

        $idEstadoRemoto = (int) $estadoRemoto['id_estado'];
        $estadoLocal = [
            1 => 'hora_reservada',
            2 => 'hora_confirmada',
            3 => 'hora_rechazada',
            4 => 'paciente_en_espera',
            5 => 'hora_confirmada',
            6 => 'atencion_realizada',
            7 => 'no_asiste',
        ][$idEstadoRemoto] ?? $agenda->estado;

        $agenda->update([
            'estado' => $estadoLocal,
            'fecha_hora_confirmada' => $idEstadoRemoto === 2 && ! $agenda->fecha_hora_confirmada
                ? $agenda->fecha_hora_solicitada
                : $agenda->fecha_hora_confirmada,
            'medichile_estado_id' => $idEstadoRemoto,
            'medichile_sincronizado_at' => now(),
            'medichile_sync_error' => null,
        ]);

        if ($idEstadoRemoto === 2 && $voucher->estado === 'pendiente_confirmacion') {
            $voucher->update(['estado' => 'pendiente_pago']);
        }

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'agenda_externa_estado_sincronizado',
            'usuario_tipo' => 'cliente',
            'usuario_id' => $request->user()->id,
            'descripcion' => 'Estado Med-SDI sincronizado: '.($estadoRemoto['texto_estado'] ?? $idEstadoRemoto),
            'ip' => $request->ip(),
        ]);

        // Mapea el estado real de Med-SDI a la etapa del recorrido de atención (1 a 5) para mostrarla en el dashboard.
        $demoStep = $voucher->demoRecorridoStep();

        return $this->respuestaSincronizacion(
            $request,
            $voucher,
            true,
            'Estado actualizado desde Med-SDI: '.($estadoRemoto['texto_estado'] ?? str_replace('_', ' ', $estadoLocal)),
            $demoStep,
            $estadoRemoto['texto_estado'] ?? str_replace('_', ' ', $estadoLocal)
        );
    }

    /**
     * Unifica la respuesta de sincronizarHora: JSON para el fetch AJAX del dashboard
     * (celda de estado y recorrido de atención ya renderizados) o redirect clásico si
     * la petición no pide JSON.
     */
    private function respuestaSincronizacion(Request $request, Voucher $voucher, bool $ok, string $mensaje, ?int $demoStep = null, ?string $estadoTexto = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $ok,
                'mensaje' => $mensaje,
                'voucher_id' => $voucher->id,
                'codigo' => $voucher->codigo,
                'estado_texto' => $estadoTexto,
                'estado_html' => view('partials.voucher_estado_celda', ['voucher' => $voucher->fresh(['agenda'])])->render(),
                'recorrido_html' => $demoStep ? view('partials.demo_flow_guide', ['demoStep' => $demoStep])->render() : null,
            ], $ok ? 200 : 422);
        }

        return $ok ? back()->with('ok', $mensaje) : back()->with('error', $mensaje);
    }

    public function simularPago(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);
        abort_unless((int) $voucher->cliente_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'metodo_pago' => ['required', 'in:tarjeta_credito,tarjeta_debito,transferencia,efectivo'],
        ]);

        // Cuando el backend exponga /api/paciente/pagar_bono (MEDSDI_API_PAGO_ENABLED=true),
        // el copago se cobra realmente en Med-SDI antes de activar el bono local.
        // El backend calcula el monto desde el convenio FONASA vigente; no se le envía
        // el monto local para evitar que el cliente pueda influir en el precio cobrado.
        if (config('medsdi.pago_enabled')) {
            $agenda = $voucher->agenda;
            $resultadoPago = $api->pagarBono([
                'id_hora_medica' => $agenda?->medichile_hora_medica_id,
            ]);
            if (! $resultadoPago['ok']) {
                return back()->with('error', 'Med-SDI: '.($resultadoPago['mensaje'] ?? 'no fue posible procesar el pago.'));
            }
        }

        try {
            DB::transaction(function () use ($request, $voucher, $data) {
                $voucher = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                if ($voucher->estado !== 'pendiente_pago' || ! $voucher->agenda || $voucher->agenda->estado !== 'hora_confirmada') {
                    throw new \RuntimeException('La hora debe estar confirmada antes de simular el pago.');
                }
                if ($voucher->pagos()->where('estado_pago', 'pagado')->exists()) {
                    throw new \RuntimeException('Este bono ya tiene un pago aprobado.');
                }

                VoucherPago::create([
                'voucher_id' => $voucher->id,
                'monto_pagado_usuario' => $voucher->copago_usuario,
                'metodo_pago' => $data['metodo_pago'],
                'estado_pago' => 'pagado',
                'comprobante' => 'PAGO-DEMO-'.now()->format('YmdHis').'-'.$voucher->id,
            ]);
                $voucher->update(['estado' => 'activo']);

                $qrUrl = route('vouchers.usar', $voucher->qr_token);
                VoucherDeliveryRequest::create([
                'voucher_id' => $voucher->id,
                'cliente_user_id' => $request->user()->id,
                'canal' => 'patient_whatsapp',
                'destino_tipo' => 'WhatsApp paciente',
                'destino' => $request->user()->telefono,
                'estado' => 'prepared',
                'mensaje' => 'Bono '.$voucher->codigo.' pagado. QR: '.$qrUrl,
                'action_url' => $qrUrl,
                'enviado_en' => now(),
                'metadata' => ['origen' => 'medsdi_api', 'pago' => 'simulado'],
            ]);
                VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'agenda_externa_pago_simulado',
                'usuario_tipo' => 'cliente',
                'usuario_id' => $request->user()->id,
                'descripcion' => 'Copago simulado aprobado; bono y QR activados.',
                'ip' => $request->ip(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('ok', 'Pago simulado aprobado. El bono y su QR están activos.');
    }
}
