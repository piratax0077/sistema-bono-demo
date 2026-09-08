<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\TotemLog;
use App\Models\TotemVenta;
use App\Models\Voucher;
use Illuminate\Http\Request;

class EntregaTotemController extends Controller
{
    public function entregar(Request $request, $id)
    {
        $request->validate([
            'canal' => 'required|string|in:app,whatsapp,sms,veterinario,email,impresion',
            'destino' => 'nullable|string|max:255',
        ]);

        $totem = $request->get('totem');

        if (!$totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem no autenticado',
            ], 401);
        }

        $venta = TotemVenta::with('detalles')
            ->where('id', $id)
            ->where('totem_id', $totem->id)
            ->first();

        if (!$venta) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Venta no encontrada para este tótem',
            ], 404);
        }

        if ($venta->estado !== 'pagado') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'La venta aún no está pagada',
            ], 409);
        }

        $vouchers = Voucher::where('totem_venta_id', $venta->id)->get();

        if ($vouchers->isEmpty()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'La venta está pagada, pero aún no tiene vouchers generados',
                'venta_id' => $venta->id,
            ], 404);
        }

        $venta->update([
            'canal_entrega' => $request->canal,
            'destino_entrega' => $request->destino,
            'entregado_en' => now(),
        ]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'voucher_entregado',
            'detalle' => 'Entrega por '.$request->canal.' venta ID '.$venta->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'venta_id' => $venta->id,
            'canal' => $request->canal,
            'destino' => $request->destino,
            'entregado_en' => optional($venta->fresh()->entregado_en)->format('Y-m-d H:i:s'),
            'vouchers' => $vouchers->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'codigo' => $voucher->codigo,
                    'qr_token' => $voucher->qr_token,
                    'qr_url' => route('vouchers.qr', $voucher->qr_token),
                    'valor' => $voucher->valor,
                    'vence' => optional($voucher->fecha_vencimiento)->format('Y-m-d H:i:s'),
                    'estado' => $voucher->estado,

                    'cliente_id' => $voucher->cliente_id,
                    'cliente_rut' => $voucher->cliente_rut,
                    'cliente_nombre' => $voucher->cliente_nombre,

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
                ];
            })->values(),
            'mensaje' => 'Entrega registrada correctamente',
        ]);
    }
}
