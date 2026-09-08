<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\TotemLog;
use App\Models\AuditorNotificacion;
use Illuminate\Http\Request;

class StatusTotemController extends Controller
{
    public function status()
    {
        return response()->json([
            'ok' => true,
            'service' => 'sdi-totem-api',
            'time' => now()->toIso8601String(),
        ]);
    }

    public function ping(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $totem = $request->get('totem');

        $payload = [
            'ultimo_ping' => now(),
            'ultimo_acceso' => now(),
        ];

        if ($request->filled('latitude')) {
            $payload['geolocalizacion_lat'] = $request->latitude;
        }

        if ($request->filled('longitude')) {
            $payload['geolocalizacion_lng'] = $request->longitude;
        }

        $totem->update($payload);

        return response()->json([
            'ok' => true,
            'totem' => [
                'codigo' => $totem->codigo,
                'estado_operacional' => $totem->fresh()->estado_operacional ?: 'ok',
                'ultimo_ping' => $totem->fresh()->ultimo_ping,
            ],
        ]);
    }

    public function malfunction(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $totem = $request->get('totem');
        $message = $request->message ?: 'Reporte manual de mal funcionamiento desde tótem.';

        $totem->update([
            'estado_operacional' => 'alerta',
            'ultima_alerta_at' => now(),
            'ultima_alerta_mensaje' => $message,
            'geolocalizacion_lat' => $request->filled('latitude') ? $request->latitude : $totem->geolocalizacion_lat,
            'geolocalizacion_lng' => $request->filled('longitude') ? $request->longitude : $totem->geolocalizacion_lng,
            'ultimo_ping' => now(),
            'metadata' => array_merge($totem->metadata ?: [], [
                'ultima_alerta_ip' => $request->ip(),
                'ultima_alerta_user_agent' => substr((string) $request->userAgent(), 0, 250),
            ]),
        ]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'malfuncionamiento',
            'detalle' => $message,
            'ip' => $request->ip(),
        ]);

        AuditorNotificacion::create([
            'titulo' => 'Alerta tótem: mal funcionamiento',
            'mensaje' => implode("\n", [
                'Tótem: '.$totem->codigo.' · '.$totem->nombre,
                'Ubicación: '.($totem->ubicacion ?: 'No registrada'),
                'Geolocalización: '.($request->latitude ?: 'sin latitud').', '.($request->longitude ?: 'sin longitud'),
                'Motivo informado: '.$message,
                'Acción requerida: administración debe revisar el equipo y resolver la alerta antes de confiar en nuevas operaciones del tótem.',
            ]),
            'leido' => false,
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'code' => 'TOTEM-ALERT-'.$totem->id.'-'.now()->format('YmdHis'),
                'status' => 'alerta',
                'totem' => [
                    'id' => $totem->id,
                    'codigo' => $totem->codigo,
                    'geolocalizacion_lat' => $totem->fresh()->geolocalizacion_lat,
                    'geolocalizacion_lng' => $totem->fresh()->geolocalizacion_lng,
                ],
            ],
        ]);
    }
}
