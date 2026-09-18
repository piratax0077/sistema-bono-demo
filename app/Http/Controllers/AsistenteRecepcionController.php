<?php

namespace App\Http\Controllers;

use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherPago;
use App\Services\MedichileAgendaService;
use App\Services\MedsdiAgendaApiService;
use App\Models\Voucher;
use App\Models\ClienteAutorizacion;
use App\Models\VoucherBaseDependiente;
use App\Models\VoucherBaseUsuario;
use App\Services\ClienteAuthorizationGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AsistenteRecepcionController extends Controller
{
    private function autorizarVoucherBuscado(Request $request, Voucher $voucher): void
    {
        $ids = collect($request->session()->get('asistente_recepcion_voucher_ids', []))->map(fn ($id) => (int) $id);
        abort_unless($ids->contains((int) $voucher->id), 403);
    }

    public function sincronizarHora(Request $request, Voucher $voucher, MedsdiAgendaApiService $api)
    {
        $this->autorizarVoucherBuscado($request, $voucher);
        $agenda = $voucher->agenda;
        if (!$agenda?->medichile_hora_medica_id) return back()->with('abrir_recepcion_modal', true)->with('error', 'El bono no tiene una hora Med-SDI vinculada.');
        $resultado = $api->estadoHoraMedicaComoAsistente((int) $agenda->medichile_hora_medica_id);
        if (!($resultado['ok'] ?? false)) return back()->with('abrir_recepcion_modal', true)->with('error', $resultado['mensaje'] ?? 'No fue posible consultar la hora.');
        $idEstado = (int) data_get($resultado, 'registros.id_estado', 0);
        $estadoAgenda = [1=>'hora_reservada',2=>'hora_confirmada',3=>'hora_rechazada',4=>'paciente_en_espera',5=>'hora_confirmada',6=>'atencion_realizada',7=>'no_asiste'][$idEstado] ?? $agenda->estado;
        $agenda->update(['estado'=>$estadoAgenda,'medichile_estado_id'=>$idEstado,'medichile_sincronizado_at'=>now(),'medichile_sync_error'=>null]);
        if ($idEstado === 2 && $voucher->estado === 'pendiente_confirmacion') $voucher->update(['estado'=>'pendiente_pago']);
        if ((bool) data_get($resultado, 'registros.pago_online', false)) {
            DB::transaction(function () use ($request, $voucher, $resultado) {
                $voucher = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                if (! $voucher->pagos()->where('estado_pago', 'pagado')->exists()) {
                    VoucherPago::create([
                        'voucher_id'=>$voucher->id,
                        'monto_pagado_usuario'=>$voucher->copago_usuario,
                        'metodo_pago'=>'sincronizado_medsdi',
                        'estado_pago'=>'pagado',
                        'comprobante'=>'MEDSDI-ORDEN-'.data_get($resultado, 'registros.orden_id', 'N-D'),
                    ]);
                }
                if (in_array($voucher->estado, ['pendiente_confirmacion', 'pendiente_pago'], true)) {
                    $voucher->update(['estado'=>'activo']);
                }
                VoucherAuditoria::create([
                    'voucher_id'=>$voucher->id,
                    'accion'=>'agenda_externa_pago_reconciliado',
                    'usuario_tipo'=>'asistente',
                    'usuario_id'=>$request->user()->id,
                    'descripcion'=>'Pago reconciliado desde la orden Med-SDI #'.data_get($resultado, 'registros.orden_id', 'N/D').'.',
                    'ip'=>$request->ip(),
                ]);
            });
        }
        return back()->with('abrir_recepcion_modal', true)->with('ok', 'Estado actualizado desde Med-SDI: '.data_get($resultado, 'registros.texto_estado', $estadoAgenda));
    }

    public function pagarBono(Request $request, Voucher $voucher, MedsdiAgendaApiService $medsdiApi)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);
        $this->autorizarVoucherBuscado($request, $voucher);
        $data = $request->validate(['metodo_pago'=>['required','in:tarjeta_credito,tarjeta_debito,transferencia,efectivo']]);
        $agenda = $voucher->agenda;
        if (!$agenda?->medichile_hora_medica_id) {
            return back()->with('abrir_recepcion_modal', true)->with('error', 'El bono no tiene una hora Med-SDI vinculada.');
        }
        if ($voucher->estado !== 'pendiente_pago' || $agenda->estado !== 'hora_confirmada') {
            return back()->with('abrir_recepcion_modal', true)->with('error', 'La hora debe estar confirmada antes de pagar.');
        }
        if ($voucher->pagos()->where('estado_pago', 'pagado')->exists()) {
            return back()->with('abrir_recepcion_modal', true)->with('ok', 'El bono ya se encuentra pagado.');
        }

        $resultadoRemoto = $medsdiApi->pagarBonoComoAsistente((int) $agenda->medichile_hora_medica_id, $data['metodo_pago']);
        if (!($resultadoRemoto['ok'] ?? false)) {
            return back()->with('abrir_recepcion_modal', true)
                ->with('error', 'Med-SDI no registró el pago: '.($resultadoRemoto['mensaje'] ?? 'respuesta no válida.'));
        }
        try {
            DB::transaction(function () use ($request, $voucher, $data, $resultadoRemoto) {
                $voucher = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                if ($voucher->estado !== 'pendiente_pago' || !$voucher->agenda || $voucher->agenda->estado !== 'hora_confirmada') throw new \RuntimeException('La hora debe estar confirmada antes de pagar.');
                if ($voucher->pagos()->where('estado_pago','pagado')->exists()) throw new \RuntimeException('Este bono ya se encuentra pagado.');
                VoucherPago::create(['voucher_id'=>$voucher->id,'monto_pagado_usuario'=>$voucher->copago_usuario,'metodo_pago'=>$data['metodo_pago'],'estado_pago'=>'pagado','comprobante'=>'PAGO-ASISTENTE-'.now()->format('YmdHis').'-'.$voucher->id]);
                $voucher->update(['estado'=>'activo']);
                VoucherAuditoria::create(['voucher_id'=>$voucher->id,'accion'=>'agenda_externa_pago_asistente','usuario_tipo'=>'asistente','usuario_id'=>$request->user()->id,'descripcion'=>'Copago registrado por API en Med-SDI; orden remota #'.data_get($resultadoRemoto, 'registros.id', 'N/D').'. Bono y QR locales activados.','ip'=>$request->ip()]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('abrir_recepcion_modal', true)->with('error', $exception->getMessage());
        }
        return back()->with('abrir_recepcion_modal', true)->with('ok', 'Pago registrado en Med-SDI. El bono y su QR están activos.');
    }

    public function confirmarHora(Request $request, Voucher $voucher, MedsdiAgendaApiService $medsdiApi)
    {
        $this->autorizarVoucherBuscado($request, $voucher);
        $agenda = $voucher->agenda;
        if (!$agenda?->medichile_hora_medica_id) return back()->with('abrir_recepcion_modal', true)->with('error', 'El bono no tiene una hora Med-SDI vinculada.');
        if ($voucher->estado !== 'pendiente_confirmacion' || $agenda->estado !== 'hora_reservada') return back()->with('abrir_recepcion_modal', true)->with('error', 'La hora no se encuentra pendiente de confirmación.');

        $resultado = $medsdiApi->confirmarHoraMedicaComoAsistente((int) $agenda->medichile_hora_medica_id);
        if (!($resultado['ok'] ?? false)) return back()->with('abrir_recepcion_modal', true)->with('error', $resultado['mensaje'] ?? 'Med-SDI no pudo confirmar la hora.');

        // No confiar sólo en la respuesta del comando: comprobamos el estado
        // persistido en Med-SDI antes de modificar el espejo local.
        $verificacion = $medsdiApi->estadoHoraMedicaComoAsistente((int) $agenda->medichile_hora_medica_id);
        if (!($verificacion['ok'] ?? false) || (int) data_get($verificacion, 'registros.id_estado', 0) !== 2) {
            return back()->with('abrir_recepcion_modal', true)->with(
                'error',
                'Med-SDI recibió la confirmación, pero no fue posible verificar que la hora quedara confirmada. Actualice el estado antes de continuar.'
            );
        }

        DB::transaction(function () use ($request, $voucher, $agenda, $verificacion) {
            $agenda->update(['estado' => 'hora_confirmada','fecha_hora_confirmada' => $agenda->fecha_hora_solicitada,'medichile_estado_id' => (int) data_get($verificacion, 'registros.id_estado'),'medichile_sincronizado_at' => now(),'medichile_sync_error' => null]);
            $voucher->update(['estado' => 'pendiente_pago']);
            VoucherAuditoria::create(['voucher_id'=>$voucher->id,'accion'=>'agenda_externa_hora_confirmada_asistente','usuario_tipo'=>'asistente','usuario_id'=>$request->user()->id,'descripcion'=>'Hora confirmada por asistente y verificada en Med-SDI.','ip'=>$request->ip()]);
        });

        return back()->with('abrir_recepcion_modal', true)->with('ok', 'Hora confirmada. El bono quedó pendiente de pago.');
    }

    public function buscarReserva(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate([
            'metodo' => ['required', 'in:codigo,rut'],
            'codigo' => ['nullable', 'required_if:metodo,codigo', 'string', 'max:500'],
            'rut' => ['nullable', 'required_if:metodo,rut', 'string', 'max:20'],
        ]);

        $bonos = collect();

        if ($data['metodo'] === 'codigo') {
            $request->session()->forget('asistente_recepcion_paciente');
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
            $pacienteRemoto = $medsdiApi->pacientePorRutComoAsistente($rut);
            $pacienteMedsdi = $pacienteRemoto['paciente'] ?? null;
            $paciente = $this->normalizarPacienteMedsdi($pacienteMedsdi, $rut)
                ?: $this->resolverPaciente($rut);
            $resultado = $medsdiApi->horasVigentesPorRut($rut);
            if (! $resultado['ok'] && ! $paciente) {
                return back()->withInput()->with('abrir_recepcion_modal', true)
                    ->with('error', $pacienteRemoto['mensaje'] ?? $resultado['mensaje']);
            }

            $idsHoras = collect($resultado['registros'] ?? [])
                ->map(fn ($hora) => (int) ($hora['id_hora_medica'] ?? $hora['id'] ?? 0))
                ->filter()->unique()->values()->all();
            $sha = hash('sha256', $rut);
            $hmac = hash_hmac('sha256', $rut, (string) config('app.key'));
            $bonos = Voucher::with(['agenda', 'profesional', 'servicio', 'beneficiarioDependiente', 'beneficiarioBaseUsuario'])
                ->where(function ($query) use ($idsHoras, $sha, $hmac) {
                    if ($idsHoras !== []) {
                        $query->whereHas('agenda', fn ($agenda) => $agenda->whereIn('medichile_hora_medica_id', $idsHoras));
                    }
                    $metodo = $idsHoras === [] ? 'where' : 'orWhere';
                    $query->{$metodo}(fn ($voucher) => $voucher
                        ->whereIn('beneficiario_rut_hash', [$sha, $hmac])
                        ->orWhere('cliente_rut_hash', $sha));
                })
                ->get();
            $rutTitularBono = $bonos->first()?->cliente_rut_visible;
            $paciente = $this->normalizarPacienteMedsdi($pacienteMedsdi, $rut, $rutTitularBono)
                ?: $paciente;
            $request->session()->put('asistente_recepcion_paciente', $paciente ?: [
                'tipo' => 'desconocido', 'nombre' => null, 'rut' => $rut,
                'parentesco' => null, 'titular_nombre' => null, 'titular_rut' => null,
            ]);
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

    public function solicitarAutorizacionPaciente(
        Request $request,
        Voucher $voucher,
        ClienteAuthorizationGate $authorizationGate
    ) {
        abort_unless(config('demo.enabled'), 404);
        $this->autorizarVoucherBuscado($request, $voucher);

        $titularRut = $voucher->cliente_rut_visible;
        $clienteId = $authorizationGate->clienteIdForRut($titularRut, $voucher->cliente_id);
        if (! $clienteId) {
            return back()->with('abrir_recepcion_modal', true)
                ->with('error', 'No se encontró la ficha del titular responsable para enviar la autorización.');
        }

        $existente = ClienteAutorizacion::query()
            ->where('cliente_id', $clienteId)
            ->where('tipo_accion', 'recepcion_bono_paciente')
            ->where('referencia_tipo', 'voucher')
            ->where('referencia_id', $voucher->id)
            ->where('estado', 'pendiente')
            ->where('expira_at', '>', now())
            ->latest('id')
            ->first();
        $resultado = $existente
            ? ['autorizacion' => $existente]
            : $authorizationGate->requestAuthorization(
                $clienteId,
                'recepcion_bono_paciente',
                'voucher',
                $voucher->id,
                $request,
                [
                    'canal' => 'escritorio_asistente',
                    'bono' => $voucher->codigo,
                    'beneficiario' => $voucher->beneficiario_nombre ?: $voucher->cliente_nombre,
                    'beneficiario_tipo' => $voucher->beneficiario_tipo ?: 'titular',
                    'beneficiario_rut' => $voucher->beneficiario_rut_visible,
                    'parentesco' => $voucher->beneficiario_parentesco,
                    'titular' => $voucher->cliente_nombre,
                    'titular_rut' => $titularRut,
                    'servicio_nombre' => $voucher->tipo_servicio,
                    'profesional_nombre' => $voucher->prestador_nombre,
                    'copago' => (float) $voucher->copago_usuario,
                ]
            );
        $autorizacion = $resultado['autorizacion'] ?? null;
        if (! $autorizacion) {
            return back()->with('abrir_recepcion_modal', true)
                ->with('error', $resultado['mensaje'] ?? 'No fue posible enviar la solicitud a la App del paciente.');
        }

        return back()->with('abrir_app_paciente_modal', true)
            ->with('autorizacion_app_paciente_id', $autorizacion->id);
    }

    public function responderAutorizacionPaciente(Request $request, ClienteAutorizacion $autorizacion)
    {
        abort_unless(config('demo.enabled'), 404);
        abort_unless($autorizacion->tipo_accion === 'recepcion_bono_paciente', 404);
        $ids = collect($request->session()->get('asistente_recepcion_voucher_ids', []))->map(fn ($id) => (int) $id);
        abort_unless($autorizacion->referencia_tipo === 'voucher' && $ids->contains((int) $autorizacion->referencia_id), 403);
        $data = $request->validate(['respuesta' => ['required', 'in:aprobar,rechazar']]);

        if ($autorizacion->estado !== 'pendiente' || ($autorizacion->expira_at && now()->gte($autorizacion->expira_at))) {
            if ($autorizacion->estado === 'pendiente') $autorizacion->update(['estado' => 'expirada']);
            return back()->with('abrir_recepcion_modal', true)->with('error', 'La solicitud ya fue respondida o expiró.');
        }

        $aprobada = $data['respuesta'] === 'aprobar';
        $autorizacion->update([
            'estado' => $aprobada ? 'aprobada' : 'rechazada',
            'aprobada_at' => $aprobada ? now() : null,
            'rechazada_at' => $aprobada ? null : now(),
        ]);
        \App\Helpers\SecurityLogger::log(
            'respuesta_app_paciente_simulada_'.$autorizacion->fresh()->estado,
            'ClienteAutorizacion', $autorizacion->id, $autorizacion->fresh()->estado,
            'Respuesta simulada desde la App móvil del titular responsable', $autorizacion->cliente_id
        );

        return back()->with('abrir_recepcion_modal', true)->with(
            $aprobada ? 'ok' : 'error',
            $aprobada ? 'El paciente autorizó el bono desde la App simulada.' : 'El paciente rechazó el bono desde la App simulada.'
        );
    }

    private function resolverPaciente(string $rut): ?array
    {
        $sha = hash('sha256', $rut);
        $hmac = hash_hmac('sha256', $rut, (string) config('app.key'));
        $dependiente = VoucherBaseDependiente::with('usuario')
            ->whereIn('estado', ['activo', 'vigente'])
            ->where(fn ($query) => $query->whereNull('vigente_desde')->orWhere('vigente_desde', '<=', now()->toDateString()))
            ->where(fn ($query) => $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()->toDateString()))
            ->where(fn ($query) => $query->whereIn('rut_hash', [$hmac, $sha])->orWhere('rut_sha256', $sha))
            ->first();

        if ($dependiente && $dependiente->usuario && $this->registroBaseVigente($dependiente->usuario)) {
            return [
                'tipo' => 'dependiente', 'nombre' => $dependiente->nombre, 'rut' => $rut,
                'parentesco' => $dependiente->parentesco ?: 'Carga',
                'titular_nombre' => $dependiente->usuario->nombre,
                'titular_rut' => $this->descifrar($dependiente->usuario->rut_encrypted),
            ];
        }

        $titular = VoucherBaseUsuario::query()
            ->whereIn('estado', ['activo', 'vigente'])
            ->where(fn ($query) => $query->whereNull('vigente_desde')->orWhere('vigente_desde', '<=', now()->toDateString()))
            ->where(fn ($query) => $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()->toDateString()))
            ->where(fn ($query) => $query->whereIn('rut_hash', [$hmac, $sha])->orWhere('rut_sha256', $sha))
            ->first();
        if (! $titular) return null;

        return [
            'tipo' => 'titular', 'nombre' => $titular->nombre, 'rut' => $rut,
            'parentesco' => 'Titular', 'titular_nombre' => $titular->nombre, 'titular_rut' => $rut,
        ];
    }

    private function normalizarPacienteMedsdi(mixed $paciente, string $rut, ?string $rutTitularEsperado = null): ?array
    {
        if (! is_array($paciente)) return null;

        $esDependiente = ($paciente['tipo'] ?? null) === 'dependiente'
            || (bool) ($paciente['es_dependiente'] ?? false);
        $titular = is_array($paciente['titular'] ?? null) ? $paciente['titular'] : null;
        $responsables = collect(is_array($paciente['responsables'] ?? null) ? $paciente['responsables'] : []);
        if ($rutTitularEsperado) {
            $rutEsperado = strtoupper((string) preg_replace('/[^0-9K]/i', '', $rutTitularEsperado));
            $titularBono = $responsables->first(function ($responsable) use ($rutEsperado) {
                $rutResponsable = strtoupper((string) preg_replace('/[^0-9K]/i', '', (string) ($responsable['rut'] ?? '')));
                return $rutResponsable !== '' && hash_equals($rutEsperado, $rutResponsable);
            });
            if (is_array($titularBono)) $titular = $titularBono;
        }

        return [
            'tipo' => $esDependiente ? 'dependiente' : 'titular',
            'nombre' => $paciente['nombre_completo'] ?? trim(implode(' ', array_filter([
                $paciente['nombres'] ?? null,
                $paciente['apellido_uno'] ?? null,
                $paciente['apellido_dos'] ?? null,
            ]))),
            'rut' => $paciente['rut'] ?? $rut,
            'parentesco' => $esDependiente ? ($paciente['parentesco'] ?? 'Carga') : 'Titular',
            'titular_nombre' => $esDependiente ? ($titular['nombre_completo'] ?? null) : ($paciente['nombre_completo'] ?? null),
            'titular_rut' => $esDependiente ? ($titular['rut'] ?? null) : ($paciente['rut'] ?? $rut),
        ];
    }

    private function descifrar(?string $valor): ?string
    {
        if (! filled($valor)) return null;
        try { return Crypt::decryptString($valor); } catch (Throwable) { return $valor; }
    }

    private function registroBaseVigente(VoucherBaseUsuario $usuario): bool
    {
        return in_array($usuario->estado, ['activo', 'vigente'], true)
            && (! $usuario->vigente_desde || $usuario->vigente_desde->lte(today()))
            && (! $usuario->vigente_hasta || $usuario->vigente_hasta->gte(today()));
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
