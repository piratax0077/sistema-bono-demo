<?php

namespace App\Http\Controllers;

use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Services\MedichileAgendaService;
use App\Services\MedsdiAgendaApiService;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AsistenteRecepcionController extends Controller
{
    public function buscarReserva(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate([
            'metodo' => ['required', 'in:codigo,rut'],
            'codigo' => ['nullable', 'required_if:metodo,codigo', 'string', 'max:500'],
            'rut' => ['nullable', 'required_if:metodo,rut', 'string', 'max:20'],
        ]);

        $bonos = collect();

        if ($data['metodo'] === 'codigo') {
            $valor = trim((string) $data['codigo']);
            $path = parse_url($valor, PHP_URL_PATH);
            $token = $valor;
            if (is_string($path) && $path !== '') {
                $segmentos = array_values(array_filter(explode('/', trim($path, '/'))));
                $token = (string) end($segmentos);
                if (in_array($token, ['usar', 'lector-demo', 'whatsapp-demo'], true) && count($segmentos) > 1) {
                    $token = $segmentos[count($segmentos) - 2];
                }
            }

            $bonos = Voucher::with(['agenda', 'profesional', 'servicio'])
                ->where(function ($query) use ($valor, $token) {
                    $query->where('codigo', $valor)->orWhere('qr_token', $token);
                })->get();
        } else {
            $rut = strtoupper((string) preg_replace('/[^0-9K]/i', '', $data['rut']));
            $resultado = $medsdiApi->horasVigentesPorRut($rut);
            if (! $resultado['ok']) {
                return back()->withInput()->with('abrir_recepcion_modal', true)->with('error', $resultado['mensaje']);
            }

            $idsHoras = collect($resultado['registros'] ?? [])
                ->map(fn ($hora) => (int) ($hora['id_hora_medica'] ?? $hora['id'] ?? 0))
                ->filter()->unique()->values()->all();
            $bonos = Voucher::with(['agenda', 'profesional', 'servicio'])
                ->whereHas('agenda', fn ($query) => $query->whereIn('medichile_hora_medica_id', $idsHoras))
                ->get();
        }

        $bonos = $bonos->filter(function ($bono) {
            return ! $bono->qr_usado
                && ! in_array($bono->estado, ['cobrado', 'usado', 'invalidado_cliente'], true)
                && $bono->agenda
                && $bono->agenda->estado !== 'paciente_en_espera';
        })->values();

        if ($bonos->isEmpty()) {
            $request->session()->forget('asistente_recepcion_voucher_ids');
            return back()->withInput()->with('abrir_recepcion_modal', true)
                ->with('error', 'No se encontraron horas vigentes pendientes de llegada para los datos ingresados.');
        }

        $request->session()->put('asistente_recepcion_voucher_ids', $bonos->pluck('id')->all());

        return redirect()->route('asistente.escritorio')
            ->with('abrir_recepcion_modal', true)
            ->with('ok', 'Paciente reconocido. Seleccione la hora que desea enviar a sala de espera.');
    }

    public function recibirQr(Request $request, MedichileAgendaService $medichileAgenda)
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string', 'min:40', 'max:500'],
            'cliente_rut' => ['nullable', 'string', 'max:30'],
            'canal_recepcion' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            return DB::transaction(function () use ($request, $data, $medichileAgenda) {
                $voucher = \App\Models\Voucher::where('qr_token', $data['qr_token'])
                    ->lockForUpdate()
                    ->first();

                if (! $voucher) {
                    return response()->json(['ok' => false, 'message' => 'QR no encontrado.'], 404);
                }

                if ($voucher->qr_usado || in_array($voucher->estado, ['cobrado', 'usado', 'invalidado_cliente'], true)) {
                    return response()->json(['ok' => false, 'message' => 'El QR ya fue utilizado o no está vigente.'], 422);
                }

                if (! empty($data['cliente_rut'])) {
                    $rutIngresado = strtoupper(preg_replace('/[^0-9K]/i', '', $data['cliente_rut']));
                    $rutVoucher = strtoupper(preg_replace('/[^0-9K]/i', '', $voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible));
                    if ($rutIngresado !== $rutVoucher) {
                        return response()->json(['ok' => false, 'message' => 'El RUT ingresado no corresponde al paciente del QR.'], 422);
                    }
                }

                $agendaExistente = VoucherAgenda::where('voucher_id', $voucher->id)->lockForUpdate()->first();
                $sync = $medichileAgenda->dejarPacienteEnEspera($voucher, $agendaExistente);
                $ahora = now();
                $canal = $data['canal_recepcion'] ?? 'lector_recepcion';

                $agenda = VoucherAgenda::updateOrCreate(['voucher_id' => $voucher->id], [
                    'cliente_id' => $voucher->cliente_id,
                    'profesional_id' => $voucher->profesional_id,
                    'fecha_hora_solicitada' => optional($agendaExistente)->fecha_hora_solicitada ?: $ahora,
                    'fecha_hora_confirmada' => $ahora,
                    'estado' => 'paciente_en_espera',
                    'observacion' => 'QR recibido por '.($request->user()->name ?? 'asistente').' mediante '.$canal.'.',
                    'medichile_hora_medica_id' => $sync['hora_medica_id'],
                    'medichile_estado_id' => $sync['estado_id'],
                    'medichile_sincronizado_at' => $sync['sincronizado_at'],
                    'medichile_sync_error' => null,
                ]);

                $delivery = VoucherDeliveryRequest::where('voucher_id', $voucher->id)
                    ->where('canal', 'assistant_totem_reception')
                    ->latest('id')
                    ->first();
                if ($delivery) {
                    $metadata = $delivery->metadata ?: [];
                    $metadata['estado_paciente'] = 'esperando_atencion';
                    $metadata['canal_recepcion_qr'] = $canal;
                    $metadata['medichile_hora_medica_id'] = $sync['hora_medica_id'];
                    $metadata['medichile_estado'] = $sync['estado_nombre'];
                    $metadata['recibido_en'] = $ahora->toIso8601String();
                    $metadata['recibido_por_user_id'] = $request->user()->id;
                    $delivery->update(['estado' => 'received', 'metadata' => $metadata]);
                }

                $voucher->update(['agenda_id' => $agenda->id, 'estado' => 'asignado']);

                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'qr_recibido_profesional_resuelto_automaticamente',
                    'usuario_tipo' => $request->user()->rol,
                    'usuario_id' => $request->user()->id,
                    'descripcion' => 'QR recibido por '.$canal.'. Profesional '.$voucher->prestador_nombre.' resuelto desde el bono. Hora Medichile #'.$sync['hora_medica_id'].' actualizada a Espera.',
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'ok' => true,
                    'data' => [
                        'code' => $voucher->codigo,
                        'status' => $voucher->estado,
                        'agenda_status' => 'waiting_room',
                        'professional_id' => $voucher->profesional_id,
                        'professional_name' => $voucher->prestador_nombre,
                        'patient_rut' => $voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible,
                        'medichile_appointment_id' => $sync['hora_medica_id'],
                        'message' => 'QR validado. Profesional reconocido y paciente en espera en Medichile.',
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('No fue posible recibir el QR del paciente.', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function enviarRecepcionExterna(Request $request, VoucherDeliveryRequest $delivery)
    {
        $data = $request->validate([
            'telefono_recepcion' => ['required', 'string', 'max:30'],
        ], [
            'telefono_recepcion.required' => 'Ingrese el WhatsApp que le entregó la institución.',
        ]);

        $delivery = VoucherDeliveryRequest::with('voucher')->findOrFail($delivery->id);

        if ($delivery->canal !== 'assistant_totem_reception' || ! $delivery->voucher) {
            return back()->with('error', 'Este bono no pertenece a la bandeja de recepción.');
        }

        $telefono = preg_replace('/\D+/', '', $data['telefono_recepcion']);
        if (strlen($telefono) < 8 || strlen($telefono) > 15) {
            return back()->withErrors([
                'telefono_recepcion' => 'Ingrese un teléfono válido con código de país, por ejemplo +56912345678.',
            ]);
        }

        $voucher = $delivery->voucher;
        $qrUrl = route('vouchers.validarPantalla', $voucher->qr_token);
        $mensaje = 'SDI: bono '.$voucher->codigo.' del paciente '.$voucher->cliente_nombre.'. Recepción segura: '.$qrUrl;
        $whatsappUrl = 'https://web.whatsapp.com/send?phone='.$telefono.'&text='.rawurlencode($mensaje);

        VoucherDeliveryRequest::create([
            'voucher_id' => $voucher->id,
            'cliente_user_id' => $voucher->cliente_id,
            'canal' => 'external_reception_whatsapp',
            'destino_tipo' => 'WhatsApp recepción externa',
            'destino' => $telefono,
            'estado' => 'prepared',
            'mensaje' => $mensaje,
            'action_url' => $whatsappUrl,
            'enviado_en' => now(),
            'metadata' => [
                'entrega' => 'recepcion_externa_sin_sistema',
                'recepcion_origen_id' => $delivery->id,
                'enviado_por_user_id' => $request->user()->id,
            ],
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'qr_enviado_recepcion_externa_whatsapp',
            'usuario_tipo' => $request->user()->rol,
            'usuario_id' => $request->user()->id,
            'descripcion' => 'La recepción envió el QR al WhatsApp externo '.$telefono.' porque la institución no utiliza el sistema de recepción.',
            'ip' => $request->ip(),
        ]);

        return back()
            ->with('ok', 'Envío preparado para el WhatsApp de la recepción externa.')
            ->with('reception_whatsapp_url', $whatsappUrl);
    }

    public function dejarEnEspera(
        Request $request,
        VoucherDeliveryRequest $delivery,
        MedichileAgendaService $medichileAgenda,
        MedsdiAgendaApiService $medsdiApi
    )
    {
        try {
            return DB::transaction(function () use ($request, $delivery, $medichileAgenda, $medsdiApi) {
                $delivery = VoucherDeliveryRequest::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
                $voucher = $delivery->voucher()->lockForUpdate()->firstOrFail();

                if ($delivery->canal !== 'assistant_totem_reception') {
                    return back()->with('error', 'Este registro no pertenece a la bandeja de recepción.');
                }

                $agendaExistente = VoucherAgenda::where('voucher_id', $voucher->id)->lockForUpdate()->first();

                if ($delivery->estado !== 'received'
                    && ($voucher->qr_usado || in_array($voucher->estado, ['cobrado', 'usado', 'invalidado_cliente'], true))) {
                    return back()->with('error', 'El bono ya no está disponible para recepción.');
                }

                // Medichile se actualiza primero: la recepción no se confirma localmente
                // si la hora médica real no pudo quedar en estado Espera.
                if (! $voucher->profesional_id && $agendaExistente?->medichile_hora_medica_id) {
                    $rutPaciente = (string) ($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible);
                    $resultado = $medsdiApi->confirmarLlegadaSalaEspera(
                        (int) $agendaExistente->medichile_hora_medica_id,
                        $rutPaciente
                    );
                    if (! $resultado['ok']) {
                        throw new \RuntimeException($resultado['mensaje']);
                    }
                    $sync = [
                        'hora_medica_id' => (int) $agendaExistente->medichile_hora_medica_id,
                        'estado_id' => (int) ($resultado['registros']['id_estado'] ?? 4),
                        'estado_nombre' => 'Espera',
                        'sincronizado_at' => now(),
                    ];
                } else {
                    $sync = $medichileAgenda->dejarPacienteEnEspera($voucher, $agendaExistente);
                }
                $llegada = now();
                $agenda = VoucherAgenda::updateOrCreate(
                    ['voucher_id' => $voucher->id],
                    [
                        'cliente_id' => $voucher->cliente_id,
                        'profesional_id' => $voucher->profesional_id,
                        'fecha_hora_solicitada' => $agendaExistente->fecha_hora_solicitada ?? $llegada,
                        'fecha_hora_confirmada' => $llegada,
                        'estado' => 'paciente_en_espera',
                        'observacion' => 'Paciente recibido por '.($request->user()->name ?? 'asistente').' y enviado a sala de espera.',
                        'medichile_hora_medica_id' => $sync['hora_medica_id'],
                        'medichile_estado_id' => $sync['estado_id'],
                        'medichile_sincronizado_at' => $sync['sincronizado_at'],
                        'medichile_sync_error' => null,
                    ]
                );

                $metadata = $delivery->metadata ?: [];
                $metadata['estado_paciente'] = 'esperando_atencion';
                $metadata['recibido_en'] = $metadata['recibido_en'] ?? $llegada->toIso8601String();
                $metadata['recibido_por_user_id'] = $request->user()->id;
                $metadata['medichile_hora_medica_id'] = $sync['hora_medica_id'];
                $metadata['medichile_estado'] = $sync['estado_nombre'];

                $delivery->update([
                    'estado' => 'received',
                    'metadata' => $metadata,
                ]);

                $voucher->update([
                    'agenda_id' => $agenda->id,
                    'estado' => 'asignado',
                ]);

                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'agenda_medichile_sincronizada_espera',
                    'usuario_tipo' => $request->user()->rol,
                    'usuario_id' => $request->user()->id,
                    'descripcion' => 'Paciente dejado en espera. Hora Medichile #'.$sync['hora_medica_id'].' actualizada al estado real Espera (ID '.$sync['estado_id'].'). Profesional '.$voucher->prestador_nombre.'. Llegada '.$llegada->format('d-m-Y H:i:s').'.',
                    'ip' => $request->ip(),
                ]);

                return back()
                    ->with('abrir_recepcion_modal', true)
                    ->with('ok', 'Paciente reconocido. La hora #'.$sync['hora_medica_id'].' quedó en estado Espera en la agenda real de Medichile.');
            });
        } catch (Throwable $exception) {
            Log::error('No fue posible sincronizar la espera con Medichile.', [
                'delivery_id' => $delivery->id,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->with('abrir_recepcion_modal', true)
                ->with('error', 'No se cambió la recepción: '.$exception->getMessage());
        }
    }
}
