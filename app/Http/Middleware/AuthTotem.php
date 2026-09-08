<?php

namespace App\Http\Middleware;

use App\Models\Totem;
use App\Models\TotemSesion;
use Closure;
use Illuminate\Http\Request;

class AuthTotem
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) $request->header('X-TOTEM-TOKEN');

        if ($token === '') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Token no enviado',
            ], 401);
        }

        $tokenHash = hash('sha256', $token);
        $totem = Totem::where('token_hash', $tokenHash)
            ->where('activo', true)
            ->first();

        if (!$totem || !$totem->token_expira_at || now()->gt($totem->token_expira_at)) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Sesión de tótem inválida o expirada',
            ], 401);
        }

        if (config('security.require_totem_ip') &&
            $totem->ip_autorizada &&
            !hash_equals((string) $totem->ip_autorizada, (string) $request->ip())) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'IP de tótem no autorizada',
            ], 403);
        }

        $session = TotemSesion::where('totem_id', $totem->id)
            ->where('token_hash', $tokenHash)
            ->whereNull('fin')
            ->where('expira_at', '>', now())
            ->first();

        if (!$session) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Sesión de tótem cerrada',
            ], 401);
        }

        $totem->update([
            'ultimo_acceso' => now(),
            'ultimo_ping' => now(),
        ]);

        $request->merge(['totem' => $totem]);

        return $next($request);
    }
}
