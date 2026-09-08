<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use App\Services\ClienteAuthorizationGate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssistantVoucherController extends Controller
{
    public function catalog()
    {
        return response()->json([
            'ok' => true,
            'data' => [
                'services' => VoucherServicio::where('activo', true)
                    ->orderBy('nombre')
                    ->get()
                    ->map(fn ($service) => [
                        'id' => $service->id,
                        'name' => $service->nombre,
                        'price' => (float) $service->valor_base,
                        'copay' => (float) $service->copago_base,
                    ])
                    ->values(),
                'providers' => VoucherProfesional::where('activo', true)
                    ->orderBy('nombre')
                    ->get()
                    ->map(fn ($provider) => [
                        'id' => $provider->id,
                        'name' => $provider->nombre,
                        'rut' => $provider->rut,
                        'specialty' => $provider->especialidad,
                        'email' => $provider->email,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function resolveClient(Request $request)
    {
        $data = $request->validate([
            'rut' => 'required|string|max:30',
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
        ]);

        $rutNormalizado = $this->normalizarRut($data['rut']);

        $user = User::whereRaw(
            "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?",
            [$rutNormalizado]
        )->first();

        if (! $user) {
            return response()->json([
                'ok' => false,
                'message' => 'Beneficiario no inscrito. Debe existir antes de vender un bono.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'rut' => $user->rut,
                'email' => $user->email,
                'phone' => $user->telefono,
            ],
        ]);
    }

    public function store(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $data = $request->validate([
            'client_id' => 'required|integer|exists:users,id',
            'provider_id' => 'required|integer|exists:voucher_profesionales,id',
            'service_id' => 'required|integer|exists:voucher_servicios,id',
            'patient_name' => 'nullable|string|max:150',
            'purchaser_name' => 'nullable|string|max:150',
            'purchaser_phone' => 'nullable|string|max:50',
            'office_number' => 'nullable|string|max:80',
            'payment_method' => 'nullable|string|max:60',
            'emission_latitude' => 'nullable|numeric|between:-90,90',
            'emission_longitude' => 'nullable|numeric|between:-180,180',
            'cliente_authorization_token' => 'nullable|string|min:40',
        ]);

        $client = User::findOrFail($data['client_id']);
        $provider = VoucherProfesional::where('activo', true)->findOrFail($data['provider_id']);
        $service = VoucherServicio::where('activo', true)->findOrFail($data['service_id']);

        $external = $this->validarBaseExterna($client, $provider, $service);
        if (! $external['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $external['motivo'],
            ], 422);
        }

        $clienteAutorizacionId = $authorizationGate->clienteIdForRut($client->rut, $client->id);
        if (! $clienteAutorizacionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe ficha de beneficiario para autorizar esta compra en la app.',
            ], 422);
        }

        $authorization = $authorizationGate->ensureApprovedOrRequest(
            $request,
            $clienteAutorizacionId,
            'compra_bono_asistente',
            'asistente_venta_bono',
            null,
            [
                'canal' => 'asistente',
                'rut' => $client->rut,
                'beneficiario' => $client->name,
                'servicio_id' => $service->id,
                'servicio_nombre' => $service->nombre,
                'profesional_id' => $provider->id,
                'profesional_nombre' => $provider->nombre,
                'copago' => $service->copago_base,
                'valor_total' => $service->valor_base,
                'office_number' => $data['office_number'] ?? null,
            ]
        );

        if (! $authorization['ok']) {
            $status = $authorization['estado'] === 'pendiente' ? 202 : 403;
            $status = $authorization['estado'] === 'expirada' ? 410 : $status;

            return response()->json([
                'ok' => false,
                'requires_client_authorization' => true,
                'estado_autorizacion' => $authorization['estado'],
                'message' => $authorization['mensaje'],
                'data' => [
                    'requires_client_authorization' => true,
                    'authorization' => ($authorization['autorizacion'] ?? null)
                        ? $authorizationGate->authorizationPayload($authorization['autorizacion'])
                        : null,
                ],
            ], $status);
        }

        $otp = random_int(100000, 999999);
        $voucher = Voucher::create([
            'codigo' => $this->codigoVoucher(),
            'qr_token' => Str::random(80),
            'qr_expira' => now()->addDays(30),
            'qr_usado' => false,
            'cliente_id' => $client->id,
            'cliente_rut' => $client->rut,
            'cliente_rut_hash' => hash('sha256', $this->normalizarRut($client->rut)),
            'cliente_nombre' => $data['patient_name'] ?: $client->name,
            'tipo_servicio' => $service->nombre,
            'servicio_id' => $service->id,
            'valor' => $service->valor_base,
            'valor_total' => $service->valor_base,
            'copago_usuario' => $service->copago_base,
            'saldo_veterinario' => max(((float) $service->valor_base) - ((float) $service->copago_base) - ((float) ($service->comision_veterchile ?? 0)), 0),
            'comision_veterchile' => $service->comision_veterchile ?? 0,
            'porcentaje_descuento' => 100,
            'estado' => 'activo',
            'fecha_vencimiento' => now()->addDays(30),
            'profesional_id' => $provider->id,
            'prestador_rut' => $provider->rut,
            'prestador_nombre' => $provider->nombre,
            'prestador_especialidad' => $provider->especialidad,
            'prestador_email' => $provider->email,
            'prestador_telefono' => $provider->telefono,
            'otp_hash' => hash('sha256', (string) $otp),
            'otp_expira' => now()->addMinutes(10),
        ]);

        $voucher->update([
            'qr_firma' => hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key')),
        ]);

        $authorization['autorizacion']->update([
            'referencia_tipo' => 'voucher',
            'referencia_id' => $voucher->id,
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'voucher_emitido_asistente_autorizado_app',
            'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'asistente_api',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Bono emitido por asistente con autorizacion app '.$authorization['autorizacion']->id,
            'ip' => $request->ip(),
        ]);

        $qrUrl = route('vouchers.qr', $voucher->qr_token);

        $response = [
            'id' => $voucher->id,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->codigo,
                'status' => $voucher->estado,
            ],
            'qr_token' => $voucher->qr_token,
            'qr_url' => $qrUrl,
            'qr_image' => $this->qrImage($qrUrl),
            'cliente_authorization' => $authorizationGate->authorizationPayload($authorization['autorizacion']),
        ];

        if (app()->environment(['local', 'testing'])) {
            $response['otp_demo'] = $otp;
        }

        return response()->json([
            'ok' => true,
            'data' => $response,
        ], 201);
    }

    public function deliver(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'channel' => 'required|string|in:patient_whatsapp,patient_email,provider_email,medical_center,manual',
            'qr_token' => 'nullable|string',
        ]);

        $qrUrl = route('vouchers.qr', $voucher->qr_token);
        $message = 'SDI: bono '.$voucher->codigo.' disponible. QR seguro: '.$qrUrl;
        $destination = null;
        $actionUrl = null;

        if ($data['channel'] === 'patient_whatsapp') {
            $destination = $this->normalizarTelefono(optional($voucher->cliente)->telefono);
            $actionUrl = $destination
                ? 'https://web.whatsapp.com/send?phone='.$destination.'&text='.rawurlencode($message)
                : null;
        }

        if ($data['channel'] === 'patient_email') {
            $destination = optional($voucher->cliente)->email;
        }

        if ($data['channel'] === 'provider_email') {
            $destination = $voucher->prestador_email;
        }

        VoucherDeliveryRequest::create([
            'voucher_id' => $voucher->id,
            'cliente_user_id' => $voucher->cliente_id,
            'canal' => $data['channel'],
            'destino_tipo' => $data['channel'],
            'destino' => $destination,
            'estado' => $actionUrl ? 'prepared' : 'simulated',
            'mensaje' => $message,
            'action_url' => $actionUrl,
            'enviado_en' => now(),
            'metadata' => [
                'qr_token' => $voucher->qr_token,
                'qr_url' => $qrUrl,
                'cliente_rut' => $voucher->cliente_rut,
                'cliente_nombre' => $voucher->cliente_nombre,
                'profesional_nombre' => $voucher->prestador_nombre,
            ],
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'status' => $actionUrl ? 'prepared' : 'simulated',
                'action_url' => $actionUrl,
            ],
        ]);
    }

    public function receiveQr(Request $request)
    {
        $data = $request->validate([
            'qr_token' => 'required|string|min:40',
            'provider_id' => 'required|integer|exists:voucher_profesionales,id',
            'client_rut' => 'nullable|string|max:30',
            'appointment_reference' => 'nullable|string|max:100',
            'agenda_status_from' => 'nullable|string|max:50',
        ]);

        $voucher = Voucher::where('qr_token', $data['qr_token'])->first();

        if (! $voucher) {
            return response()->json(['ok' => false, 'message' => 'QR no encontrado.'], 404);
        }

        if ($voucher->estado !== 'activo' || $voucher->qr_usado) {
            return response()->json(['ok' => false, 'message' => 'QR no esta activo o ya fue usado.'], 422);
        }

        if ((int) $voucher->profesional_id !== (int) $data['provider_id']) {
            return response()->json(['ok' => false, 'message' => 'El QR no corresponde al profesional de esta agenda.'], 422);
        }

        if (! empty($data['client_rut']) &&
            ! hash_equals($this->normalizarRut($voucher->cliente_rut), $this->normalizarRut($data['client_rut']))) {
            return response()->json(['ok' => false, 'message' => 'El RUT del paciente no coincide con el QR.'], 422);
        }

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'qr_recibido_asistente_sala_espera',
            'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'asistente_api',
            'usuario_id' => auth()->id(),
            'descripcion' => 'QR recibido por asistente. Agenda '.$data['appointment_reference'].' cambia a paciente con pago y sala de espera.',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'code' => $voucher->codigo,
                'status' => $voucher->estado,
                'agenda_status' => 'waiting_room',
                'message' => 'QR validado. Paciente con pago confirmado y en sala de espera.',
            ],
        ]);
    }

    private function validarBaseExterna(User $user, VoucherProfesional $profesional, VoucherServicio $servicio): array
    {
        $usuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))
            ->first();

        if (! $usuario) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma al beneficiario vigente.'];
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

        return ['ok' => true, 'motivo' => 'Relacion vigente validada.'];
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

    private function qrImage(string $url): string
    {
        $svg = QrCode::format('svg')
            ->size(384)
            ->margin(1)
            ->generate($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
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
}
