<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagoAutorizacion;
use App\Models\VoucherAuditoria;
use Illuminate\Http\Request;

class PagoAutorizacionController extends Controller
{
    public function aprobar(Request $request, $token)
    {
        $aut = PagoAutorizacion::where('token', $token)->firstOrFail();

        if ($aut->estado !== 'pendiente') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización ya procesada',
                'estado' => $aut->estado,
            ], 409);
        }

        if ($aut->expira_at && now()->gt($aut->expira_at)) {
            $aut->update([
                'estado' => 'expirada',
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización expirada',
            ], 410);
        }

        $aut->update([
            'estado' => 'aprobada',
            'ip_respuesta' => $request->ip(),
            'device_id' => $request->device_id,
            'aprobada_at' => now(),
        ]);

        if ($aut->liquidacion) {
            $aut->liquidacion->update([
                'estado' => 'pendiente_revision_admin',
            ]);
        }

        if ($aut->rendicion) {
            $aut->rendicion->update([
                'estado' => 'autorizada_por_profesional',
            ]);
        }

        VoucherAuditoria::create([
            'accion' => 'liquidacion_autorizada_profesional',
            'usuario_tipo' => 'profesional',
            'usuario_id' => $aut->profesional_id,
            'descripcion' => 'Liquidación autorizada desde dispositivo profesional',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Liquidación autorizada correctamente',
            'autorizacion' => $aut->fresh(),
        ]);
    }

    public function rechazar(Request $request, $token)
    {
        $aut = PagoAutorizacion::where('token', $token)->firstOrFail();

        if ($aut->estado !== 'pendiente') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización ya procesada',
                'estado' => $aut->estado,
            ], 409);
        }

        $aut->update([
            'estado' => 'rechazada',
            'ip_respuesta' => $request->ip(),
            'device_id' => $request->device_id,
            'rechazada_at' => now(),
        ]);

        if ($aut->liquidacion) {
            $aut->liquidacion->update([
                'estado' => 'rechazada_por_profesional',
            ]);
        }

        if ($aut->rendicion) {
            $aut->rendicion->update([
                'estado' => 'rechazada_por_profesional',
            ]);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Liquidación rechazada por profesional',
            'autorizacion' => $aut->fresh(),
        ]);
    }
}
