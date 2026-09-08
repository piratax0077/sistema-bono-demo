<?php

namespace App\Http\Controllers\Api\Totem;

use App\Helpers\SecurityLogger;
use App\Http\Controllers\Controller;
use App\Models\ClienteAutorizacion;
use App\Models\VoucherMascota;
use Illuminate\Http\Request;

class MascotaSeguraTotemController extends Controller
{
    public function duenoProtegido(Request $request, $id)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $mascota = VoucherMascota::find($id);

        if (!$mascota) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Mascota no encontrada',
            ], 404);
        }

        $autorizacion = ClienteAutorizacion::where('token', $request->token)
            ->where('cliente_id', $mascota->cliente_id)
            ->where('tipo_accion', 'ver_datos_dueno')
            ->where('referencia_tipo', 'mascota')
            ->where('referencia_id', $mascota->id)
            ->first();

        if (!$autorizacion) {
            SecurityLogger::log(
                'intento_ver_dueno_sin_autorizacion',
                'VoucherMascota',
                $mascota->id,
                'rechazado',
                'Intento de ver datos del dueño sin autorización válida',
                $mascota->cliente_id
            );

            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización requerida',
            ], 403);
        }

        if ($autorizacion->estado !== 'aprobada') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización no aprobada',
                'estado' => $autorizacion->estado,
            ], 403);
        }

        if ($autorizacion->expira_at && now()->gt($autorizacion->expira_at)) {
            $autorizacion->update([
                'estado' => 'expirada',
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Autorización expirada',
            ], 403);
        }

        SecurityLogger::log(
            'datos_dueno_visualizados',
            'VoucherMascota',
            $mascota->id,
            'ok',
            'Datos sensibles del dueño visualizados con autorización aprobada',
            $mascota->cliente_id
        );

        return response()->json([
            'ok' => true,
            'mascota_id' => $mascota->id,
            'dueno' => [
                'rut' => $mascota->dueno_rut,
                'nombre' => $mascota->dueno_nombre,
                'telefono' => $mascota->dueno_telefono,
                'email' => $mascota->dueno_email,
            ],
        ]);
    }
}
