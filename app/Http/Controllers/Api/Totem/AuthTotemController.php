<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\Totem;
use App\Models\TotemLog;
use App\Models\TotemSesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTotemController extends Controller
{
    public function login(Request $request)
    {
        if ($request->filled('code') && !$request->filled('codigo')) {
            $request->merge(['codigo' => $request->input('code')]);
        }

        if ($request->filled('secret') && !$request->filled('clave_instalacion')) {
            $request->merge(['clave_instalacion' => $request->input('secret')]);
        }

        $request->validate([
            'codigo' => 'required|string|max:100',
            'clave_instalacion' => 'nullable|string|min:12|max:200',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $totem = Totem::where('codigo', strtoupper(trim($request->codigo)))->first();

        if (!$totem) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem no registrado',
            ], 404);
        }

        if (!$totem->activo) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Tótem inactivo',
            ], 403);
        }

        if (config('security.require_totem_ip') &&
            $totem->ip_autorizada &&
            !hash_equals((string) $totem->ip_autorizada, (string) $request->ip())) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'IP no autorizada',
            ], 403);
        }

        if ($totem->auth_secret_hash) {
            if (!$request->filled('clave_instalacion') ||
                !Hash::check($request->clave_instalacion, $totem->auth_secret_hash)) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Credencial de instalación inválida',
                ], 401);
            }
        } elseif (app()->environment('production')) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El tótem requiere una clave de instalación',
            ], 503);
        }

        $token = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $tokenHash = hash('sha256', $token);
        $expiresAt = now()->addMinutes(config('security.totem_session_minutes'));

        TotemSesion::where('totem_id', $totem->id)
            ->whereNull('fin')
            ->update(['fin' => now()]);

        $totem->update([
            'token' => null,
            'token_hash' => $tokenHash,
            'token_expira_at' => $expiresAt,
            'ultimo_acceso' => now(),
            'ultimo_ping' => now(),
            'geolocalizacion_lat' => $request->filled('latitude') ? $request->latitude : $totem->geolocalizacion_lat,
            'geolocalizacion_lng' => $request->filled('longitude') ? $request->longitude : $totem->geolocalizacion_lng,
        ]);

        TotemSesion::create([
            'totem_id' => $totem->id,
            'token' => '',
            'token_hash' => $tokenHash,
            'inicio' => now(),
            'expira_at' => $expiresAt,
            'ip' => $request->ip(),
        ]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'login',
            'detalle' => 'Inicio de sesión seguro del tótem',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'expira_at' => $expiresAt->toIso8601String(),
            'totem' => [
                'id' => $totem->id,
                'codigo' => $totem->codigo,
                'nombre' => $totem->nombre,
                'ubicacion' => $totem->ubicacion,
                'version' => $totem->version,
                'estado_operacional' => $totem->fresh()->estado_operacional ?: 'ok',
            ],
        ]);
    }
}
