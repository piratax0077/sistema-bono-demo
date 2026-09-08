<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\TotemLog;
use App\Models\TotemVenta;
use App\Models\Voucher;
use App\Services\ClienteAuthorizationGate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PagoTotemController extends Controller
{
    public function confirmar(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $request->validate([
            'venta_id' => 'required|integer',
            'codigo_transaccion' => 'required|string|max:100',
            'medio_pago' => 'required|string|max:50',
            'cliente_authorization_token' => 'nullable|string|min:40',
        ]);

        if (! config('payments.allow_demo')) {
            $secret = (string) config('payments.webhook_secret');
            $provided = (string) $request->header('X-PAYMENT-SIGNATURE');
            $payload = implode('|', [
                $request->venta_id,
                $request->codigo_transaccion,
                $request->medio_pago,
            ]);
            $expected = hash_hmac('sha256', $payload, $secret);

            if ($secret === '' || $provided === '' || ! hash_equals($expected, $provided)) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Confirmacion de pago no autenticada',
                ], 403);
            }
        }

        $totem = $request->get('totem');

        if (! $totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Totem no autenticado',
            ], 401);
        }

        return DB::transaction(function () use ($request, $totem, $authorizationGate) {
            $venta = TotemVenta::with('detalles')
                ->where('id', $request->venta_id)
                ->where('totem_id', $totem->id)
                ->lockForUpdate()
                ->first();

            if (! $venta) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Venta no encontrada para este totem',
                ], 404);
            }

            if ($venta->estado === 'pagado') {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La venta ya esta pagada',
                ], 409);
            }

            $clienteAutorizacionId = $authorizationGate->clienteIdForRut(
                $venta->cliente_rut,
                $venta->cliente_id
            );

            if (! $clienteAutorizacionId) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'No existe ficha de beneficiario para autorizar esta compra.',
                ], 422);
            }

            $tokenAutorizacion = $request->cliente_authorization_token
                ?: data_get($venta->metadata ?: [], 'cliente_authorization_token');

            $authorization = $authorizationGate->verifyApproved(
                $tokenAutorizacion,
                $clienteAutorizacionId,
                'compra_bono_totem'
            );

            if (! $authorization['ok']) {
                $status = $authorization['estado'] === 'pendiente' ? 202 : 403;
                $status = $authorization['estado'] === 'expirada' ? 410 : $status;

                $venta->update([
                    'estado' => 'pendiente_autorizacion_cliente',
                    'metadata' => array_merge($venta->metadata ?: [], [
                        'cliente_authorization_estado' => $authorization['estado'],
                    ]),
                ]);

                return response()->json([
                    'ok' => false,
                    'requiere_autorizacion_cliente' => true,
                    'estado_autorizacion' => $authorization['estado'],
                    'mensaje' => $authorization['mensaje'],
                    'cliente_authorization' => ($authorization['autorizacion'] ?? null)
                        ? $authorizationGate->authorizationPayload($authorization['autorizacion'])
                        : null,
                ], $status);
            }

            if ($venta->detalles->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La venta no tiene productos o vouchers asociados',
                ], 422);
            }

            $venta->update([
                'estado' => 'pagado',
                'medio_pago' => $request->medio_pago,
                'codigo_transaccion' => $request->codigo_transaccion,
                'metadata' => array_merge($venta->metadata ?: [], [
                    'cliente_authorization_id' => $authorization['autorizacion']->id,
                    'cliente_authorization_token' => $authorization['autorizacion']->token,
                    'cliente_authorization_estado' => 'aprobada',
                    'cliente_authorization_aprobada_at' => optional($authorization['autorizacion']->aprobada_at)->toIso8601String(),
                ]),
            ]);

            $vouchersGenerados = [];

            foreach ($venta->detalles as $detalle) {
                if ($detalle->tipo !== 'voucher') {
                    continue;
                }

                $codigo = $this->generarCodigoVoucher();
                $qrToken = Str::random(80);
                $otp = random_int(100000, 999999);

                $dataVoucher = [
                    'totem_venta_id' => $venta->id,
                    'codigo' => $codigo,
                    'qr_token' => $qrToken,
                    'valor' => $detalle->precio,
                    'porcentaje_descuento' => 100,
                    'estado' => 'activo',
                    'fecha_vencimiento' => now()->addDays(30),
                    'tipo_servicio' => 'Voucher Totem',
                    'servicio_id' => $detalle->referencia_id,
                    'cliente_id' => $venta->cliente_id,
                    'cliente_rut' => $venta->cliente_rut,
                    'cliente_nombre' => $venta->cliente_nombre ?: 'Invitado',
                    'qr_expira' => now()->addDays(30),
                    'qr_usado' => 0,
                    'otp_hash' => hash('sha256', (string) $otp),
                    'otp_expira' => now()->addMinutes(10),
                    'mascota_nombre' => $venta->mascota_nombre,
                    'mascota_edad' => $venta->mascota_edad,
                    'mascota_raza' => $venta->mascota_raza,
                    'prestador_rut' => $venta->prestador_rut,
                    'prestador_nombre' => $venta->prestador_nombre,
                    'prestador_especialidad' => $venta->prestador_especialidad,
                    'prestador_email' => $venta->prestador_email,
                    'prestador_telefono' => $venta->prestador_telefono,
                    'prestador_direccion' => $venta->prestador_direccion,
                    'valor_total' => $venta->valor_total ?: $detalle->precio,
                    'copago_usuario' => $venta->copago_cliente ?: 0,
                    'saldo_veterinario' => $venta->copago_seguro ?: 0,
                ];

                if ($venta->prestador_tipo === 'veterinario') {
                    $dataVoucher['profesional_id'] = $venta->prestador_id;
                }

                if (in_array($venta->prestador_tipo, ['farmacia', 'alimentos', 'petshop'], true)) {
                    $dataVoucher['vendedor_id'] = $venta->prestador_id;
                }

                $voucher = Voucher::create($dataVoucher);

                $voucher->update([
                    'qr_firma' => hash_hmac(
                        'sha256',
                        $voucher->id.$voucher->codigo,
                        config('app.key')
                    ),
                ]);

                $qrUrl = route('vouchers.qr', $voucher->qr_token);
                $deliveryOptions = $this->deliveryOptions($venta);

                $vouchersGenerados[] = [
                    'id' => $voucher->id,
                    'codigo' => $voucher->codigo,
                    'code' => $voucher->codigo,
                    'qr_token' => $voucher->qr_token,
                    'qr_url' => $qrUrl,
                    'qr_validacion_url' => $qrUrl,
                    'qr_image' => $this->qrImage($qrUrl),
                    'valor' => $voucher->valor,
                    'vence' => $this->dateValue($voucher->fecha_vencimiento, 'Y-m-d'),
                    'expires_at' => $this->isoDateValue($voucher->fecha_vencimiento),
                    'status' => $voucher->estado,
                    'cliente_id' => $venta->cliente_id,
                    'cliente_rut' => $venta->cliente_rut,
                    'cliente_nombre' => $voucher->cliente_nombre,
                    'client' => [
                        'id' => $venta->cliente_id,
                        'rut' => $venta->cliente_rut,
                        'name' => $voucher->cliente_nombre,
                        'phone' => $venta->cliente_telefono,
                        'email' => $venta->cliente_email,
                    ],
                    'emision' => [
                        'lat' => $venta->emision_lat,
                        'lng' => $venta->emision_lng,
                        'office_number' => $venta->office_number,
                        'client_ip' => $venta->client_ip,
                        'comprador_nombre' => $venta->comprador_nombre,
                        'comprador_telefono' => $venta->comprador_telefono,
                        'hora' => optional($venta->created_at)->toIso8601String(),
                        'forma_pago' => $venta->medio_pago,
                    ],
                    'mascota_nombre' => $voucher->mascota_nombre,
                    'mascota_edad' => $voucher->mascota_edad,
                    'mascota_raza' => $voucher->mascota_raza,
                    'prestador_nombre' => $voucher->prestador_nombre,
                    'prestador_rut' => $voucher->prestador_rut,
                    'prestador_especialidad' => $voucher->prestador_especialidad,
                    'prestador_email' => $voucher->prestador_email,
                    'prestador_telefono' => $voucher->prestador_telefono,
                    'prestador_direccion' => $voucher->prestador_direccion,
                    'provider' => [
                        'id' => $venta->prestador_id,
                        'rut' => $voucher->prestador_rut,
                        'name' => $voucher->prestador_nombre,
                        'specialty' => $voucher->prestador_especialidad,
                        'email' => $voucher->prestador_email,
                        'phone' => $voucher->prestador_telefono,
                    ],
                    'valor_total' => $voucher->valor_total,
                    'copago_cliente' => $voucher->copago_usuario,
                    'copago_seguro' => $voucher->saldo_veterinario,
                    'delivery_options' => $deliveryOptions,
                    'cliente_authorization' => $authorizationGate->authorizationPayload($authorization['autorizacion']),
                ];

                if (app()->environment(['local', 'testing'])) {
                    $vouchersGenerados[count($vouchersGenerados) - 1]['otp_demo'] = $otp;
                }
            }

            TotemLog::create([
                'totem_id' => $totem->id,
                'evento' => 'pago_confirmado',
                'detalle' => 'Pago confirmado venta ID '.$venta->id.' con '.count($vouchersGenerados).' voucher(s) generado(s)',
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'ok' => true,
                'venta_id' => $venta->id,
                'estado' => $venta->fresh()->estado,
                'total' => $venta->total,
                'codigo_transaccion' => $venta->codigo_transaccion,
                'cliente_authorization' => $authorizationGate->authorizationPayload($authorization['autorizacion']),
                'vouchers' => $vouchersGenerados,
                'delivery_options' => $this->deliveryOptions($venta),
                'data' => [
                    'voucher' => $vouchersGenerados[0] ?? null,
                    'qr_token' => $vouchersGenerados[0]['qr_token'] ?? null,
                    'qr_image' => $vouchersGenerados[0]['qr_image'] ?? null,
                    'delivery_options' => $this->deliveryOptions($venta),
                ],
            ]);
        });
    }

    private function generarCodigoVoucher(): string
    {
        do {
            $codigo = 'VT-'.now()->format('YmdHis').'-'.strtoupper(Str::random(5));
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

    private function deliveryOptions(TotemVenta $venta): array
    {
        $phone = $this->normalizePhone($venta->cliente_telefono);

        return [
            'patient_whatsapp' => [
                'available' => (bool) $phone,
                'destination' => $phone,
            ],
            'patient_email' => [
                'available' => (bool) $venta->cliente_email,
                'destination' => $venta->cliente_email ?: null,
            ],
            'provider_email' => [
                'available' => (bool) $venta->prestador_email,
                'destination' => $venta->prestador_email ?: null,
            ],
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            $digits = '56'.$digits;
        }

        return $digits;
    }

    private function dateValue($value, string $format): ?string
    {
        return $value ? Carbon::parse($value)->format($format) : null;
    }

    private function isoDateValue($value): ?string
    {
        return $value ? Carbon::parse($value)->toIso8601String() : null;
    }
}
