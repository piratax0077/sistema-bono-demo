<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\TotemLog;
use App\Models\TotemVenta;
use App\Models\TotemVentaDetalle;
use App\Models\VoucherServicio;
use App\Services\ClienteAuthorizationGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaTotemController extends Controller
{
    public function crear(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tipo' => 'required|string|in:voucher,alimentos,juguetes,planes,producto,servicio',
            'items.*.referencia_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'required|numeric|min:0',

            'medio_pago' => 'nullable|string|max:50',

            'cliente_id' => 'nullable|integer',
            'cliente_rut' => 'nullable|string|max:30',
            'cliente_nombre' => 'nullable|string|max:150',
            'cliente_telefono' => 'nullable|string|max:50',
            'cliente_email' => 'nullable|email|max:150',
            'comprador_nombre' => 'nullable|string|max:150',
            'comprador_telefono' => 'nullable|string|max:50',
            'office_number' => 'nullable|string|max:80',
            'emision_lat' => 'nullable|numeric|between:-90,90',
            'emision_lng' => 'nullable|numeric|between:-180,180',

            'prestador_tipo' => 'nullable|string|in:veterinario,farmacia,alimentos,petshop',
            'prestador_id' => 'nullable|integer',
            'prestador_nombre' => 'nullable|string|max:150',
            'prestador_rut' => 'nullable|string|max:30',
            'prestador_especialidad' => 'nullable|string|max:150',
            'prestador_email' => 'nullable|email|max:150',
            'prestador_telefono' => 'nullable|string|max:50',
            'prestador_direccion' => 'nullable|string|max:255',

            'mascota_nombre' => 'nullable|string|max:100',
            'mascota_edad' => 'nullable|integer|min:0|max:80',
            'mascota_raza' => 'nullable|string|max:100',

            'valor_total' => 'nullable|numeric|min:0',
            'copago_seguro' => 'nullable|numeric|min:0',
            'copago_cliente' => 'nullable|numeric|min:0',
        ]);

        $totem = $request->get('totem');

        if (!$totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem no autenticado',
            ], 401);
        }

        $authorizationPayload = null;

        $venta = DB::transaction(function () use ($request, $totem, $authorizationGate, &$authorizationPayload) {
            $items = collect($request->input('items', []));

            $items = $items->map(function ($item) {
                if (!in_array($item['tipo'], ['voucher', 'servicio'], true)) {
                    throw ValidationException::withMessages([
                        'items' => 'El tótem de atención sólo admite servicios autorizados.',
                    ]);
                }

                $servicio = VoucherServicio::where('id', $item['referencia_id'])
                    ->where('activo', true)
                    ->first();

                if (!$servicio) {
                    throw ValidationException::withMessages([
                        'items' => 'El servicio seleccionado no está disponible.',
                    ]);
                }

                $precio = (float) $servicio->valor_base;
                if (abs((float) $item['precio'] - $precio) > 0.001) {
                    throw ValidationException::withMessages([
                        'items' => 'El precio del servicio fue actualizado. Intente nuevamente.',
                    ]);
                }

                $item['tipo'] = 'voucher';
                $item['precio'] = $precio;

                return $item;
            });

            $total = $items->sum(function ($item) {
                return ((int) $item['cantidad']) * ((float) $item['precio']);
            });

            $valorTotal = $request->filled('valor_total')
                ? (float) $request->valor_total
                : (float) $total;

            $copagoSeguro = $request->filled('copago_seguro')
                ? (float) $request->copago_seguro
                : 0;

            $copagoCliente = $request->filled('copago_cliente')
                ? (float) $request->copago_cliente
                : max($valorTotal - $copagoSeguro, 0);

            $venta = TotemVenta::create([
                'totem_id' => $totem->id,
                'cliente_id' => $request->cliente_id,
                'total' => $total,
                'estado' => 'pendiente_autorizacion_cliente',
                'medio_pago' => $request->medio_pago,

                'cliente_rut' => $request->cliente_rut,
                'cliente_nombre' => $request->cliente_nombre ?: 'Invitado',
                'cliente_telefono' => $request->cliente_telefono,
                'cliente_email' => $request->cliente_email,
                'comprador_nombre' => $request->comprador_nombre,
                'comprador_telefono' => $request->comprador_telefono,
                'office_number' => $request->office_number,
                'emision_lat' => $request->emision_lat,
                'emision_lng' => $request->emision_lng,
                'client_ip' => $request->ip(),
                'metadata' => [
                    'totem_codigo' => $totem->codigo,
                    'user_agent' => substr((string) $request->userAgent(), 0, 250),
                    'creada_en' => now()->toIso8601String(),
                ],

                'prestador_tipo' => $request->prestador_tipo,
                'prestador_id' => $request->prestador_id,
                'prestador_nombre' => $request->prestador_nombre,
                'prestador_rut' => $request->prestador_rut,
                'prestador_especialidad' => $request->prestador_especialidad,
                'prestador_email' => $request->prestador_email,
                'prestador_telefono' => $request->prestador_telefono,
                'prestador_direccion' => $request->prestador_direccion,

                'mascota_nombre' => $request->mascota_nombre,
                'mascota_edad' => $request->mascota_edad,
                'mascota_raza' => $request->mascota_raza,

                'valor_total' => $valorTotal,
                'copago_seguro' => $copagoSeguro,
                'copago_cliente' => $copagoCliente,
            ]);

            $clienteAutorizacionId = $authorizationGate->clienteIdForRut(
                $request->cliente_rut,
                $request->cliente_id
            );

            if (! $clienteAutorizacionId) {
                throw ValidationException::withMessages([
                    'cliente' => 'No existe ficha de beneficiario para autorizar esta compra en la app.',
                ]);
            }

            $authorization = $authorizationGate->requestAuthorization(
                $clienteAutorizacionId,
                'compra_bono_totem',
                'totem_venta',
                $venta->id,
                $request,
                [
                    'canal' => 'totem',
                    'totem_codigo' => $totem->codigo,
                    'venta_id' => $venta->id,
                    'rut' => $request->cliente_rut,
                    'beneficiario' => $request->cliente_nombre ?: 'Invitado',
                    'prestador_id' => $request->prestador_id,
                    'prestador_nombre' => $request->prestador_nombre,
                    'valor_total' => $valorTotal,
                    'copago_cliente' => $copagoCliente,
                    'office_number' => $request->office_number,
                ]
            );

            if (! ($authorization['autorizacion'] ?? null)) {
                throw ValidationException::withMessages([
                    'cliente' => $authorization['mensaje'] ?? 'No se pudo solicitar autorizacion a la app del beneficiario.',
                ]);
            }

            $authorizationPayload = $authorizationGate->authorizationPayload($authorization['autorizacion']);
            $venta->update([
                'metadata' => array_merge($venta->metadata ?: [], [
                    'cliente_authorization_id' => $authorization['autorizacion']->id,
                    'cliente_authorization_token' => $authorization['autorizacion']->token,
                    'cliente_authorization_estado' => 'pendiente',
                ]),
            ]);

            foreach ($items as $item) {
                TotemVentaDetalle::create([
                    'venta_id' => $venta->id,
                    'tipo' => $item['tipo'],
                    'referencia_id' => $item['referencia_id'],
                    'cantidad' => (int) $item['cantidad'],
                    'precio' => (float) $item['precio'],
                ]);
            }

            TotemLog::create([
                'totem_id' => $totem->id,
                'evento' => 'venta_creada',
                'detalle' => 'Venta creada desde tótem ID '.$venta->id,
                'ip' => $request->ip(),
            ]);

            return $venta->load('detalles');
        });

        return response()->json([
            'ok' => true,
            'venta_id' => $venta->id,
            'estado' => $venta->estado,
            'requiere_autorizacion_cliente' => true,
            'cliente_authorization' => $authorizationPayload,
            'total' => $venta->total,
            'valor_total' => $venta->valor_total,
            'copago_seguro' => $venta->copago_seguro,
            'copago_cliente' => $venta->copago_cliente,
            'prestador_nombre' => $venta->prestador_nombre,
            'cliente_nombre' => $venta->cliente_nombre,
            'office_number' => $venta->office_number,
            'emision_lat' => $venta->emision_lat,
            'emision_lng' => $venta->emision_lng,
            'items' => $venta->detalles,
        ]);
    }
}
