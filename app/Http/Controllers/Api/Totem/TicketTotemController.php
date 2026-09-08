<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\TotemLog;
use App\Models\TotemVenta;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketTotemController extends Controller
{
    public function ticket(Request $request, $id)
    {
        $totem = $request->get('totem');

        if (!$totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem no autenticado',
            ], 401);
        }

        $venta = TotemVenta::where('id', $id)
            ->where('totem_id', $totem->id)
            ->first();

        if (!$venta) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Venta no encontrada para este tótem',
            ], 404);
        }

        $voucher = Voucher::where('totem_venta_id', $venta->id)
            ->latest()
            ->first();

        if (!$voucher) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'La venta aún no tiene voucher generado',
                'venta' => [
                    'id' => $venta->id,
                    'total' => $venta->total,
                    'estado' => $venta->estado,
                ],
            ], 404);
        }

        $qrUrl = route('vouchers.qr', $voucher->qr_token);

        return response()->json([
            'ok' => true,
            'venta' => [
                'id' => $venta->id,
                'total' => $venta->total,
                'estado' => $venta->estado,
                'medio_pago' => $venta->medio_pago,
                'codigo_transaccion' => $venta->codigo_transaccion,
                'cliente_nombre' => $venta->cliente_nombre,
                'cliente_rut' => $venta->cliente_rut,
                'prestador_nombre' => $venta->prestador_nombre,
            ],
            'voucher' => [
                'id' => $voucher->id,
                'codigo' => $voucher->codigo,
                'code' => $voucher->codigo,
                'qr_token' => $voucher->qr_token,
                'qr_url' => $qrUrl,
                'qr_validacion_url' => $qrUrl,
                'qr_image' => $this->qrImage($qrUrl),
                'valor' => $voucher->valor,
              'vence' => $voucher->fecha_vencimiento ? date('Y-m-d', strtotime($voucher->fecha_vencimiento)) : null,
'fecha_vencimiento' => $voucher->fecha_vencimiento ? date('Y-m-d', strtotime($voucher->fecha_vencimiento)) : null,
'qr_expira' => $voucher->qr_expira ? date('Y-m-d', strtotime($voucher->qr_expira)) : null,
                'estado' => $voucher->estado,
                'status' => $voucher->estado,

                'cliente_id' => $voucher->cliente_id,
                'cliente_rut' => $voucher->cliente_rut,
                'cliente_nombre' => $voucher->cliente_nombre,
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

                'valor_total' => $voucher->valor_total ?? $voucher->valor,
                'copago_cliente' => $voucher->copago_usuario ?? 0,
                'copago_seguro' => $voucher->saldo_veterinario ?? 0,
                'delivery_options' => $this->deliveryOptions($venta),
            ],
        ]);
    }

    public function deliver(Request $request, Voucher $voucher)
    {
        $request->validate([
            'channel' => 'required|string|in:patient_whatsapp,patient_email,provider_email',
            'qr_token' => 'nullable|string|max:200',
        ]);

        $totem = $request->get('totem');

        if (!$totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem no autenticado',
            ], 401);
        }

        if ($request->filled('qr_token') && !hash_equals((string) $voucher->qr_token, (string) $request->qr_token)) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El token QR no corresponde al bono indicado',
            ], 422);
        }

        $venta = TotemVenta::where('id', $voucher->totem_venta_id)
            ->where('totem_id', $totem->id)
            ->first();

        if (!$venta) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El bono no pertenece a este tótem',
            ], 403);
        }

        $qrUrl = route('vouchers.qr', $voucher->qr_token);
        $message = 'SDI: su bono '.$voucher->codigo.' está disponible. QR seguro: '.$qrUrl.'. Vence el '.$this->dateValue($voucher->fecha_vencimiento, 'd-m-Y').'.';
        $channel = $request->channel;
        $destination = null;
        $status = 'prepared';
        $actionUrl = null;

        if ($channel === 'patient_whatsapp') {
            $destination = $this->normalizePhone($venta->cliente_telefono);

            if (!$destination) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El paciente no tiene WhatsApp o teléfono registrado',
                ], 422);
            }

            $actionUrl = 'https://web.whatsapp.com/send?phone='.$destination.'&text='.rawurlencode($message);
        }

        if ($channel === 'patient_email') {
            $destination = $venta->cliente_email;
            $status = 'simulated';
        }

        if ($channel === 'provider_email') {
            $destination = $venta->prestador_email;
            $status = 'simulated';
        }

        if (!$destination) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No existe destino configurado para este canal',
            ], 422);
        }

        $venta->update([
            'canal_entrega' => $channel,
            'destino_entrega' => $destination,
            'entregado_en' => now(),
        ]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'voucher_entrega_preparada',
            'detalle' => 'Bono '.$voucher->codigo.' preparado para '.$channel.' a '.$destination,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'status' => $status,
                'channel' => $channel,
                'destination' => $destination,
                'action_url' => $actionUrl,
                'message' => $message,
            ],
        ]);
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
}
