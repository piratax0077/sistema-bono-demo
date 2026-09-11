<?php

namespace App\Http\Controllers;

use App\Models\ClienteSaldo;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use App\Models\PersonaBusqueda;
use App\Models\AgendaOnlineHorario;
use App\Services\ClienteAuthorizationGate;
use App\Services\MedsdiAgendaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ClienteBonoController extends Controller
{
    public function home(MedsdiAgendaApiService $medsdiApi)
    {
        $user = auth()->user();
        $rutNormalizado = $this->normalizarRut($user->rut);

        $vouchers = Voucher::query()
            ->with(['agenda', 'pagos'])
            ->where(function ($query) use ($user, $rutNormalizado) {
                $query->where('cliente_id', $user->id);

                if ($user->rut) {
                    $query->orWhere('cliente_rut', $user->rut)
                        ->orWhereRaw(
                            "UPPER(REPLACE(REPLACE(REPLACE(cliente_rut, '.', ''), '-', ''), ' ', '')) = ?",
                            [$rutNormalizado]
                        );
                }
            })
            ->latest('id')
            ->get();

        $proximaAgenda = VoucherAgenda::query()
            ->with(['voucher', 'profesional'])
            ->whereIn('voucher_id', $vouchers->pluck('id'))
            ->whereNotIn('estado', ['cancelada', 'paciente_atendido'])
            ->where('fecha_hora_solicitada', '>=', now())
            ->orderBy('fecha_hora_solicitada')
            ->first();

        $resumen = [
            'bonos_vigentes' => $vouchers->whereNotIn('estado', ['usado', 'cobrado', 'invalidado_cliente'])->count(),
            'pendientes_pago' => $vouchers->where('estado', 'pendiente_pago')->count(),
            'pagados' => $vouchers->filter(fn ($voucher) => $voucher->pagos->contains('estado_pago', 'pagado'))->count(),
            'en_espera' => $vouchers->filter(fn ($voucher) => optional($voucher->agenda)->estado === 'paciente_en_espera')->count(),
        ];

        $perfilRemotoMedsdi = $medsdiApi->pacienteAutenticado();
        $pacienteMedsdi = $perfilRemotoMedsdi['ok'] ? $perfilRemotoMedsdi['paciente'] : null;
        return view('clientes.home', compact('user', 'proximaAgenda', 'resumen', 'perfilRemotoMedsdi', 'pacienteMedsdi'));
    }

    public function dashboard(MedsdiAgendaApiService $medsdiApi)
    {
        $user = auth()->user();
        $rutNormalizado = $this->normalizarRut($user->rut);

        $vouchers = Voucher::query()
            ->with(['agenda', 'pagos'])
            ->where(function ($query) use ($user, $rutNormalizado) {
                $query->where('cliente_id', $user->id);

                if ($user->rut) {
                    $query->orWhere('cliente_rut', $user->rut)
                        ->orWhereRaw(
                            "UPPER(REPLACE(REPLACE(REPLACE(cliente_rut, '.', ''), '-', ''), ' ', '')) = ?",
                            [$rutNormalizado]
                    );
                }
            })
            ->whereNotIn('estado', ['usado', 'cobrado'])
            ->where(function ($query) {
                $query->whereNull('qr_usado')
                    ->orWhere('qr_usado', false);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $saldos = ClienteSaldo::query()
            ->where('cliente_rut_hash', hash('sha256', $rutNormalizado))
            ->orderBy('id', 'desc')
            ->get();

        $servicios = VoucherServicio::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $profesionales = VoucherProfesional::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $conveniosCompra = $this->conveniosDisponibles($user);

        $persona = PersonaBusqueda::porRut($user->rut)->first();
        $personaEdicion = $persona
            ? DB::connection('personas_fast')->table('personas_rapidas_ediciones')
                ->where('rut_normalizado', $persona->rut_normalizado)->first()
            : null;
        $baseUsuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))->first();
        $perfilPersona = [
            'encontrada' => (bool) $persona,
            'nombre' => $persona?->nombre_completo ?: $user->name,
            'rut' => $persona?->rut_original ?: $user->rut,
            'direccion' => $this->desencriptar($personaEdicion?->direccion_encrypted)
                ?: $this->desencriptar($baseUsuario?->direccion_encrypted),
            'grupo_ingreso' => data_get($baseUsuario?->otros, 'grupo_ingreso'),
            'edad' => data_get($baseUsuario?->otros, 'edad'),
            'origen' => $persona ? 'Base Personas MySQL' : 'Usuario autenticado',
        ];
        $horariosOnline = AgendaOnlineHorario::with(['profesional', 'servicio'])
            ->where('estado', 'disponible')->where('fecha_hora', '>', now())
            ->orderBy('fecha_hora')->take(18)->get();
        $horariosOnlineJson = $horariosOnline->map(function (AgendaOnlineHorario $horario) {
            return [
                'id' => $horario->id,
                'fecha' => $horario->fecha_hora->format('d-m-Y H:i'),
                'profesional' => $horario->profesional->nombre,
                'especialidad' => $horario->profesional->especialidad,
                'servicio' => $horario->servicio->nombre,
                'valor' => (float) $horario->servicio->valor_base,
                'copago' => (float) $horario->servicio->copago_base,
                'centro' => $horario->centro_nombre,
                'lugar' => $horario->lugar_atencion,
                'direccion' => $horario->centro_direccion,
            ];
        })->values();
        $agendaOnlineResultado = session('agenda_online_voucher_id')
            ? Voucher::with(['agenda', 'pagos', 'profesional'])->find(session('agenda_online_voucher_id'))
            : null;
        $perfilRemotoMedsdi = $medsdiApi->pacienteAutenticado();
        $pacienteMedsdi = $perfilRemotoMedsdi['ok'] ? $perfilRemotoMedsdi['paciente'] : null;
        $cuentaBancariaMedsdi = $medsdiApi->cuentaBancariaPaciente();
        if ($pacienteMedsdi) {
            $perfilPersona = [
                'encontrada' => true,
                'nombre' => trim(implode(' ', array_filter([
                    $pacienteMedsdi['nombres'] ?? null,
                    $pacienteMedsdi['apellido_uno'] ?? null,
                    $pacienteMedsdi['apellido_dos'] ?? null,
                ]))),
                'rut' => $pacienteMedsdi['rut'] ?? null,
                'direccion' => $perfilPersona['direccion'],
                'grupo_ingreso' => $perfilPersona['grupo_ingreso'],
                'edad' => $pacienteMedsdi['edad'] ?? $perfilPersona['edad'],
                'origen' => 'Perfil real Med-SDI',
            ];
        }

        $vouchersAgenda = Voucher::query()
            ->where(function ($query) use ($user, $rutNormalizado) {
                $query->where('cliente_id', $user->id);

                if ($user->rut) {
                    $query->orWhere('cliente_rut', $user->rut)
                        ->orWhereRaw(
                            "UPPER(REPLACE(REPLACE(REPLACE(cliente_rut, '.', ''), '-', ''), ' ', '')) = ?",
                            [$rutNormalizado]
                        );
                }
            })
            ->whereIn('estado', ['activo', 'pagado', 'validado_atencion'])
            ->where(function ($query) {
                $query->whereNull('qr_usado')
                    ->orWhere('qr_usado', false);
            })
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        $bonosRecientesNotificables = Voucher::query()
            ->where('cliente_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('estado', ['anulado', 'rechazado'])
            ->latest('id')
            ->take(20)
            ->get(['id', 'codigo', 'tipo_servicio', 'estado', 'prestador_nombre', 'created_at']);

        $agendas = VoucherAgenda::with(['voucher', 'profesional'])
            ->whereIn('voucher_id', $vouchersAgenda->pluck('id'))
            ->orderBy('fecha_hora_solicitada', 'desc')
            ->take(20)
            ->get();

        return view('clientes.dashboard', compact(
            'user',
            'vouchers',
            'saldos',
            'servicios',
            'profesionales',
            'conveniosCompra',
            'vouchersAgenda',
            'agendas'
            ,'perfilPersona'
            ,'horariosOnline'
            ,'horariosOnlineJson'
            ,'agendaOnlineResultado'
            ,'pacienteMedsdi'
            ,'perfilRemotoMedsdi'
            ,'cuentaBancariaMedsdi'
            ,'bonosRecientesNotificables'
        ));
    }

    public function notificarBonoAndroid(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate(['voucher_id' => ['required', 'integer']]);
        $user = $request->user();
        $voucher = Voucher::query()
            ->whereKey($data['voucher_id'])
            ->where('cliente_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('estado', ['anulado', 'rechazado'])
            ->first();

        if (! $voucher) {
            return response()->json(['ok' => false, 'mensaje' => 'El bono seleccionado no existe, no es reciente o no pertenece al paciente.'], 422);
        }

        $resultado = $medsdiApi->notificarBonoAdquirido([
            'codigo' => $voucher->codigo,
            'servicio' => $voucher->tipo_servicio ?: 'Bono médico',
            'estado_bono' => $voucher->estado,
            'profesional' => $voucher->prestador_nombre,
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => ($resultado['ok'] ?? false) ? 'notificacion_android_bono_solicitada' : 'notificacion_android_bono_fallida',
            'usuario_tipo' => 'cliente', 'usuario_id' => $user->id,
            'descripcion' => $resultado['mensaje'] ?? 'Solicitud de notificación Android procesada.', 'ip' => $request->ip(),
        ]);

        return response()->json($resultado, ($resultado['ok'] ?? false) ? 200 : 422);
    }

    public function actualizarCuentaBancaria(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate([
            'cuenta_id' => ['nullable', 'integer'],
            'titular' => ['required', 'string', 'max:150'],
            'banco_id' => ['required', 'integer'],
            'tipo_cuenta' => ['required', 'string', 'max:100'],
            'numero_cuenta' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[0-9A-Za-z.-]+$/'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $resultado = $medsdiApi->actualizarCuentaBancariaPaciente($data);

        return back()
            ->with('abrir_cuenta_bancaria', true)
            ->with($resultado['ok'] ? 'ok' : 'error', $resultado['mensaje']);
    }

    public function agenda()
    {
        abort_unless(config('demo.enabled'), 404);
        $user = auth()->user();
        $rutNormalizado = $this->normalizarRut($user->rut);
        $persona = PersonaBusqueda::porRut($user->rut)->first();
        $personaEdicion = $persona
            ? DB::connection('personas_fast')->table('personas_rapidas_ediciones')
                ->where('rut_normalizado', $persona->rut_normalizado)->first()
            : null;
        $baseUsuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))->first();
        $perfilPersona = [
            'nombre' => $persona?->nombre_completo ?: $user->name,
            'rut' => $persona?->rut_original ?: $user->rut,
            'direccion' => $this->desencriptar($personaEdicion?->direccion_encrypted)
                ?: $this->desencriptar($baseUsuario?->direccion_encrypted),
            'grupo_ingreso' => data_get($baseUsuario?->otros, 'grupo_ingreso'),
            'edad' => data_get($baseUsuario?->otros, 'edad'),
        ];
        $horariosOnline = AgendaOnlineHorario::with(['profesional', 'servicio'])
            ->where('estado', 'disponible')->where('fecha_hora', '>', now())
            ->orderBy('fecha_hora')->take(30)->get();
        $voucherIds = Voucher::where(function ($query) use ($user, $rutNormalizado) {
                $query->where('cliente_id', $user->id)
                    ->orWhere('cliente_rut_hash', hash('sha256', $rutNormalizado));
            })->pluck('id');
        $agendas = VoucherAgenda::with(['voucher', 'profesional'])
            ->whereIn('voucher_id', $voucherIds)
            ->orderByDesc('fecha_hora_solicitada')->take(20)->get();
        $agendaOnlineResultado = session('agenda_online_voucher_id')
            ? Voucher::with(['agenda', 'pagos', 'profesional'])->find(session('agenda_online_voucher_id'))
            : null;

        return view('pacientes.agenda', compact('user', 'perfilPersona', 'horariosOnline', 'agendas', 'agendaOnlineResultado'));
    }

    public function comprar(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $data = $request->validate([
            'rut' => 'required|string|max:30',
            'servicio_id' => 'required|integer|exists:voucher_servicios,id',
            'profesional_id' => 'required|integer|exists:voucher_profesionales,id',
            'destino_qr' => 'required|string|in:provider_whatsapp,medical_center_whatsapp',
            'centro_medico_email' => 'nullable|email|max:150',
            'centro_medico_telefono' => 'nullable|string|max:50',
            'office_number' => 'nullable|string|max:80',
        ]);

        return $this->procesarCompra($request, $authorizationGate, $data);
    }

    public function confirmarCompraAutorizada(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $validated = $request->validate([
            'cliente_authorization_token' => 'required|string|min:40',
        ]);

        $payload = $request->session()->get($this->sessionKey($validated['cliente_authorization_token']));

        if (! $payload) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('error', 'No encontre una compra pendiente para esa autorizacion. Inicie la compra nuevamente.');
        }

        $request->merge([
            'cliente_authorization_token' => $validated['cliente_authorization_token'],
        ]);

        return $this->procesarCompra($request, $authorizationGate, $payload);
    }

    private function procesarCompra(Request $request, ClienteAuthorizationGate $authorizationGate, array $data)
    {
        $user = $request->user();
        $rutUsuario = $this->normalizarRut($user->rut);
        $rutSolicitado = $this->normalizarRut($data['rut']);

        if (! $this->rutValido($rutSolicitado)) {
            return back()->withInput()->with('error', 'RUT no válido. Revise el número y el dígito verificador.');
        }

        if (! hash_equals($rutUsuario, $rutSolicitado)) {
            return back()->withInput()->with('error', 'Usuario no encontrado. Comuníquese con su sistema de previsión.');
        }

        $servicio = VoucherServicio::where('activo', true)->findOrFail($data['servicio_id']);
        $profesional = VoucherProfesional::where('activo', true)->findOrFail($data['profesional_id']);

        if (! $this->normalizarTelefono($user->telefono)) {
            return back()->withInput()->with('error', 'El paciente debe tener un WhatsApp registrado para recibir su copia del QR.');
        }

        if ($data['destino_qr'] === 'provider_whatsapp' && ! $this->normalizarTelefono($profesional->telefono)) {
            return back()->withInput()->with('error', 'El profesional seleccionado no tiene un WhatsApp registrado. Seleccione otro profesional o envíe la segunda copia a la institución.');
        }

        if ($data['destino_qr'] === 'medical_center_whatsapp' && ! $this->normalizarTelefono($data['centro_medico_telefono'] ?? null)) {
            return back()->withInput()->withErrors([
                'centro_medico_telefono' => 'Ingrese el WhatsApp de la institución o centro médico.',
            ]);
        }

        $validacion = $this->validarBaseExterna($user, $profesional, $servicio);

        if (! $validacion['ok']) {
            VoucherAuditoria::create([
                'voucher_id' => null,
                'accion' => 'compra_beneficiario_rechazada_base_externa',
                'usuario_tipo' => 'cliente',
                'usuario_id' => $user->id,
                'descripcion' => $validacion['motivo'],
                'ip' => $request->ip(),
            ]);

            return back()->with('error', $validacion['motivo']);
        }

        $convenio = $validacion['convenio'];
        $baseUsuario = $validacion['usuario'];
        $valorConvenio = (float) $convenio['valor'];
        $copagoConvenio = (float) $convenio['copago'];

        $clienteAutorizacionId = $authorizationGate->clienteIdForUser($user);

        if (! $clienteAutorizacionId) {
            return back()->with('error', 'No existe ficha de beneficiario para autorizar esta compra en la app.');
        }

        $authorization = $authorizationGate->ensureApprovedOrRequest(
            $request,
            $clienteAutorizacionId,
            'compra_bono_beneficiario',
            'cliente_web_compra_bono',
            null,
            [
                'canal' => 'app_usuario',
                'rut' => $user->rut,
                'beneficiario' => $user->name,
                'servicio_id' => $servicio->id,
                'servicio_nombre' => $servicio->nombre,
                'profesional_id' => $profesional->id,
                'profesional_nombre' => $profesional->nombre,
                'destino_qr' => $data['destino_qr'],
                'nivel_bono' => $convenio['nivel'],
                'copago' => $copagoConvenio,
                'valor_total' => $valorConvenio,
                'office_number' => $data['office_number'] ?? null,
            ]
        );

        if (! $authorization['ok']) {
            $autorizacion = $authorization['autorizacion'] ?? null;

            if ($autorizacion && $authorization['estado'] === 'pendiente') {
                $request->session()->put($this->sessionKey($autorizacion->token), $data);

                return redirect()
                    ->route('cliente.dashboard')
                    ->with('authorization_pending_token', $autorizacion->token)
                    ->with('authorization_pending_expires', optional($autorizacion->expira_at)->format('d-m-Y H:i:s'))
                    ->with('ok', 'Solicitud enviada a la app autorizadora del beneficiario. Apruebe y luego presione continuar compra.');
            }

            return redirect()
                ->route('cliente.dashboard')
                ->with('error', $authorization['mensaje']);
        }

        $otp = random_int(100000, 999999);

        $voucher = Voucher::create([
            'codigo' => $this->codigoVoucher(),
            'qr_token' => Str::random(80),
            'qr_expira' => now()->addDays(30),
            'qr_usado' => false,
            'cliente_id' => $user->id,
            'cliente_rut' => $user->rut,
            'cliente_rut_hash' => hash('sha256', $rutUsuario),
            'cliente_nombre' => $user->name,
            'beneficiario_tipo' => 'titular',
            'beneficiario_base_usuario_id' => $baseUsuario->id,
            'beneficiario_nombre' => $baseUsuario->nombre ?: $user->name,
            'beneficiario_rut' => Crypt::encryptString($rutUsuario),
            'beneficiario_rut_hash' => $this->rutHmac($user->rut),
            'beneficiario_direccion' => $baseUsuario->direccion_encrypted,
            'beneficiario_fecha_nacimiento' => $baseUsuario->fecha_nacimiento_encrypted,
            'beneficiario_edad' => data_get($baseUsuario->otros, 'edad'),
            'tipo_servicio' => $servicio->nombre,
            'servicio_id' => $servicio->id,
            'valor' => $valorConvenio,
            'valor_total' => $valorConvenio,
            'copago_usuario' => $copagoConvenio,
            'saldo_veterinario' => max($valorConvenio - $copagoConvenio - (float) ($servicio->comision_veterchile ?? 0), 0),
            'comision_veterchile' => $servicio->comision_veterchile ?? 0,
            'porcentaje_descuento' => 100,
            'estado' => 'activo',
            'fecha_vencimiento' => now()->addDays(30),
            'profesional_id' => $profesional->id,
            'prestador_rut' => $profesional->rut,
            'prestador_nombre' => $profesional->nombre,
            'prestador_especialidad' => $profesional->especialidad,
            'prestador_email' => $profesional->email,
            'prestador_telefono' => $profesional->telefono,
            'otp_hash' => hash('sha256', (string) $otp),
            'otp_expira' => now()->addMinutes(10),
        ]);

        $voucher->update([
            'qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key')),
        ]);

        if (($authorization['autorizacion'] ?? null)) {
            $authorization['autorizacion']->update([
                'referencia_tipo' => 'voucher',
                'referencia_id' => $voucher->id,
            ]);

            $request->session()->forget($this->sessionKey($authorization['autorizacion']->token));
        }

        $deliveries = $this->registrarEntregas($voucher, $user, $profesional, $servicio, $data, $request);
        $delivery = $deliveries['principal'];
        $secondDelivery = $deliveries['segundo'];

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'compra_beneficiario_base_externa',
            'usuario_tipo' => 'cliente',
            'usuario_id' => $user->id,
            'descripcion' => 'Bono comprado con convenio nivel '.$convenio['nivel'].', valor $'.number_format($valorConvenio, 0, ',', '.').' y copago $'.number_format($copagoConvenio, 0, ',', '.').'. Autorizacion app '.(($authorization['autorizacion']->id ?? 'n/a')).'.',
            'ip' => $request->ip(),
        ]);

        $redirect = redirect()
            ->route('cliente.dashboard')
            ->with('ok', 'Bono generado: QR enviado a '.$delivery->destino_tipo.' y a '.$secondDelivery->destino_tipo.'. También quedó disponible para la asistente o el tótem.');

        if ($delivery->action_url) {
            $redirect->with('action_url', $delivery->action_url);
        }

        if (app()->environment(['local', 'testing'])) {
            $redirect->with('otp_demo', $otp);
        }

        return $redirect;
    }

    private function registrarEntregas(
        Voucher $voucher,
        $user,
        VoucherProfesional $profesional,
        VoucherServicio $servicio,
        array $data,
        Request $request
    ): array {
        $qrUrl = route('vouchers.usar', $voucher->qr_token);
        $message = 'SDI: bono '.$voucher->codigo.' disponible. QR seguro: '.$qrUrl;

        $metadata = [
            'qr_token' => $voucher->qr_token,
            'qr_url' => $qrUrl,
            'cliente_rut' => $user->rut,
            'cliente_nombre' => $user->name,
            'profesional_id' => $profesional->id,
            'profesional_rut' => $profesional->rut,
            'profesional_nombre' => $profesional->nombre,
            'especialidad' => $voucher->prestador_especialidad,
            'servicio_id' => $servicio->id,
            'servicio_nombre' => $servicio->nombre,
            'office_number' => $data['office_number'] ?? null,
            'centro_medico_email' => $data['centro_medico_email'] ?? null,
            'centro_medico_telefono' => $data['centro_medico_telefono'] ?? null,
            'ip' => $request->ip(),
        ];

        $principal = VoucherDeliveryRequest::create([
            'voucher_id' => $voucher->id,
            'cliente_user_id' => $user->id,
            'canal' => 'patient_whatsapp',
            'destino_tipo' => 'WhatsApp paciente',
            'destino' => $this->normalizarTelefono($user->telefono),
            'estado' => 'prepared',
            'mensaje' => $message,
            'action_url' => 'https://web.whatsapp.com/send?phone='.$this->normalizarTelefono($user->telefono).'&text='.rawurlencode($message),
            'enviado_en' => now(),
            'metadata' => array_merge($metadata, ['entrega' => 'principal_paciente']),
        ]);

        $segundoTelefono = $data['destino_qr'] === 'provider_whatsapp'
            ? $this->normalizarTelefono($profesional->telefono)
            : $this->normalizarTelefono($data['centro_medico_telefono'] ?? null);
        $segundoTipo = $data['destino_qr'] === 'provider_whatsapp'
            ? 'WhatsApp profesional'
            : 'WhatsApp institución / centro médico';
        $segundo = VoucherDeliveryRequest::create([
                'voucher_id' => $voucher->id,
                'cliente_user_id' => $user->id,
                'canal' => $data['destino_qr'],
                'destino_tipo' => $segundoTipo,
                'destino' => $segundoTelefono,
                'estado' => $segundoTelefono ? 'prepared' : 'simulated',
                'mensaje' => $message,
                'action_url' => $segundoTelefono ? 'https://web.whatsapp.com/send?phone='.$segundoTelefono.'&text='.rawurlencode($message) : null,
                'enviado_en' => now(),
                'metadata' => array_merge($metadata, [
                    'entrega' => 'segunda_copia_profesional_o_institucion',
                    'relacion' => 'bono_paciente_profesional_lugar_atencion',
                ]),
            ]);

        $recepcion = VoucherDeliveryRequest::create([
            'voucher_id' => $voucher->id,
            'cliente_user_id' => $user->id,
            'canal' => 'assistant_totem_reception',
            'destino_tipo' => 'Recepción asistente / tótem',
            'destino' => $data['office_number'] ?? 'Recepción general',
            'estado' => 'prepared',
            'mensaje' => 'Bono disponible para recepción del paciente. '.$message,
            'action_url' => route('vouchers.validarPantalla', $voucher->qr_token),
            'enviado_en' => now(),
            'metadata' => array_merge($metadata, [
                'entrega' => 'bandeja_recepcion',
                'disponible_para' => ['asistente', 'totem'],
                'estado_paciente' => 'pendiente_recepcion',
            ]),
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'qr_disponible_paciente_y_centro',
            'usuario_tipo' => 'sistema',
            'usuario_id' => $user->id,
            'descripcion' => 'QR enviado simultáneamente a WhatsApp propio del paciente y a '.$segundoTipo.' '.$segundoTelefono.'. Profesional '.$profesional->nombre.', lugar '.($data['office_number'] ?? 'sin box informado').'.',
            'ip' => $request->ip(),
        ]);

        return ['principal' => $principal, 'segundo' => $segundo, 'recepcion' => $recepcion];
    }

    private function validarBaseExterna($user, VoucherProfesional $profesional, VoucherServicio $servicio): array
    {
        $usuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))
            ->first();

        if (! $usuario) {
            return ['ok' => false, 'motivo' => 'Usuario no encontrado. Comuníquese con su sistema de previsión.'];
        }

        $baseProfesional = $this->vigente(VoucherBaseProfesional::where('rut_hash', $this->rutHmac($profesional->rut))
            ->where('rut_sha256', $this->rutSha256($profesional->rut)))
            ->first();

        if (! $baseProfesional) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma al profesional vigente.'];
        }

        $baseServicio = $this->vigente(VoucherBaseServicio::where('otros->voucher_servicio_id', $servicio->id))
            ->first();

        if (! $baseServicio) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma el servicio vigente.'];
        }

        $relacion = $this->vigente(VoucherBaseRelacion::where('usuario_id', $usuario->id)
            ->where('profesional_id', $baseProfesional->id)
            ->where('servicio_id', $baseServicio->id)
            ->where('estado', 'vigente'))
            ->first();

        if (! $relacion) {
            return ['ok' => false, 'motivo' => 'No existe relacion vigente beneficiario-profesional-servicio en base externa.'];
        }

        if ($relacion->requiere_auditoria) {
            return ['ok' => false, 'motivo' => $relacion->motivo_auditoria ?: 'La relacion requiere auditoria antes de emitir.'];
        }

        return [
            'ok' => true,
            'motivo' => 'Relacion vigente validada.',
            'usuario' => $usuario,
            'convenio' => $this->valoresConvenio($relacion, $baseServicio, $servicio),
        ];
    }

    private function conveniosDisponibles($user): array
    {
        $usuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))
            ->first();

        if (! $usuario) {
            return [];
        }

        return $this->vigente(VoucherBaseRelacion::with(['profesional', 'servicio'])
            ->where('usuario_id', $usuario->id)
            ->whereNotNull('profesional_id')
            ->whereNotNull('servicio_id')
            ->where('estado', 'vigente'))
            ->get()
            ->map(function (VoucherBaseRelacion $relacion) {
                $profesionalId = data_get($relacion->profesional, 'otros.voucher_profesional_id');
                $servicioId = data_get($relacion->servicio, 'otros.voucher_servicio_id');
                $profesional = VoucherProfesional::where('activo', true)->find($profesionalId);
                $servicio = VoucherServicio::where('activo', true)->find($servicioId);

                if (! $profesional || ! $servicio || $relacion->requiere_auditoria) {
                    return null;
                }

                return array_merge($this->valoresConvenio($relacion, $relacion->servicio, $servicio), [
                    'profesional_id' => $profesional->id,
                    'profesional_nombre' => $profesional->nombre,
                    'profesional_rut' => $profesional->rut,
                    'especialidad' => $relacion->servicio->especialidad ?: $profesional->especialidad ?: $servicio->nombre,
                    'servicio_id' => $servicio->id,
                    'servicio_nombre' => $servicio->nombre,
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function valoresConvenio(VoucherBaseRelacion $relacion, VoucherBaseServicio $baseServicio, VoucherServicio $servicio): array
    {
        $valor = (float) (data_get($relacion->otros, 'valor_convenio')
            ?? $baseServicio->valor_referencial
            ?? $servicio->valor_base);
        $copago = (float) (data_get($relacion->otros, 'copago_convenio')
            ?? data_get($relacion->otros, 'copago')
            ?? $servicio->copago_base);

        return [
            'nivel' => $relacion->nivel_bono ?: $baseServicio->nivel_bono ?: 'nivel_1',
            'valor' => $valor,
            'copago' => $copago,
            'cobertura' => max($valor - $copago, 0),
        ];
    }

    private function vigente($query)
    {
        return $query
            ->whereIn('estado', ['activo', 'vigente'])
            ->where(function ($builder) {
                $builder->whereNull('vigente_desde')
                    ->orWhere('vigente_desde', '<=', now()->toDateString());
            })
            ->where(function ($builder) {
                $builder->whereNull('vigente_hasta')
                    ->orWhere('vigente_hasta', '>=', now()->toDateString());
            });
    }

    private function codigoVoucher(): string
    {
        do {
            $codigo = 'SDI-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
        } while (Voucher::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function rutHmac($rut): string
    {
        return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key'));
    }

    private function rutSha256($rut): string
    {
        return hash('sha256', $this->normalizarRut($rut));
    }

    private function normalizarRut($rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }

    private function rutValido(string $rut): bool
    {
        if (! preg_match('/^(\d{7,8})([0-9K])$/', $rut, $partes)) {
            return false;
        }

        $suma = 0;
        $multiplicador = 2;
        for ($i = strlen($partes[1]) - 1; $i >= 0; $i--) {
            $suma += ((int) $partes[1][$i]) * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }
        $resultado = 11 - ($suma % 11);
        $dv = $resultado === 11 ? '0' : ($resultado === 10 ? 'K' : (string) $resultado);

        return hash_equals($dv, $partes[2]);
    }

    private function normalizarTelefono(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            $digits = '56'.$digits;
        }

        return $digits;
    }

    private function labelDestino(string $channel): string
    {
        return [
            'patient_whatsapp' => 'WhatsApp paciente',
            'patient_email' => 'Email paciente',
            'provider_email' => 'Email profesional',
            'provider_whatsapp' => 'WhatsApp profesional',
            'medical_center_whatsapp' => 'WhatsApp centro medico',
            'medical_center' => 'Centro medico / secretaria',
        ][$channel] ?? $channel;
    }

    private function sessionKey(string $token): string
    {
        return 'cliente_purchase_pending_'.hash('sha256', $token);
    }

    private function desencriptar($valor): ?string
    {
        if (! filled($valor)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $valor);
        } catch (\Throwable $exception) {
            return (string) $valor;
        }
    }
}
