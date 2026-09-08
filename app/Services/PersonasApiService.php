<?php

namespace App\Services;

use App\Models\PersonaIntegracionAuditoria;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PersonasApiService
{
    public function buscar(string $rut, ?int $totemId = null): array
    {
        $rut = $this->normalizarRut($rut);
        $correlationId = (string) Str::uuid();
        $inicio = microtime(true);

        if (! $this->configurada()) {
            return $this->noDisponible($rut, $correlationId, $inicio, $totemId, 'no_configurada');
        }

        try {
            $response = $this->request($correlationId)->get('/api/personas/'.rawurlencode($rut));
            $resultado = $this->interpretar($response, $rut, $correlationId);
            $this->auditar('buscar', $rut, $resultado['resultado'], $correlationId, $inicio, $totemId, $response->status());
            return $resultado;
        } catch (ConnectionException $e) {
            return $this->noDisponible($rut, $correlationId, $inicio, $totemId, 'sin_conexion');
        }
    }

    public function guardar(array $datos, ?int $totemId = null): array
    {
        $rut = $this->normalizarRut((string) ($datos['rut'] ?? ''));
        $correlationId = (string) Str::uuid();
        $inicio = microtime(true);

        if (! $this->configurada()) {
            return $this->noDisponible($rut, $correlationId, $inicio, $totemId, 'no_configurada', 'guardar');
        }

        try {
            $existente = $this->request($correlationId)->get('/api/personas/'.rawurlencode($rut));
            $response = $existente->successful()
                ? $this->request($correlationId)->put('/api/personas/'.rawurlencode($rut), $datos)
                : $this->request($correlationId)->post('/api/personas', $datos);
            $payload = $response->json() ?: [];
            $resultado = $response->successful() ? 'ok' : 'error_remoto';
            $this->auditar('guardar', $rut, $resultado, $correlationId, $inicio, $totemId, $response->status());
            return array_merge($payload, ['disponible' => true, 'resultado' => $resultado, 'status' => $response->status(), 'correlation_id' => $correlationId]);
        } catch (ConnectionException $e) {
            return $this->noDisponible($rut, $correlationId, $inicio, $totemId, 'sin_conexion', 'guardar');
        }
    }

    private function request(string $correlationId)
    {
        return Http::baseUrl(rtrim((string) config('personas.base_url'), '/'))
            ->acceptJson()->withToken((string) config('personas.token'))
            ->withHeaders(['X-Correlation-ID' => $correlationId])
            ->timeout(max(1, (int) config('personas.timeout', 3)))
            ->retry(1, 150, throw: false);
    }

    private function interpretar(Response $response, string $rut, string $correlationId): array
    {
        $payload = $response->json() ?: [];
        if ($response->successful() && ($payload['found'] ?? false)) {
            return array_merge($payload, ['disponible' => true, 'resultado' => 'encontrada', 'status' => $response->status(), 'correlation_id' => $correlationId]);
        }
        if ($response->status() === 404) {
            return ['ok' => true, 'found' => false, 'disponible' => true, 'resultado' => 'no_encontrada', 'rut_normalizado' => $rut, 'status' => 404, 'correlation_id' => $correlationId];
        }
        return ['ok' => false, 'found' => false, 'disponible' => true, 'resultado' => 'error_remoto', 'status' => $response->status(), 'message' => 'Personas API rechazó la consulta.', 'correlation_id' => $correlationId];
    }

    private function noDisponible(string $rut, string $correlationId, float $inicio, ?int $totemId, string $motivo, string $operacion = 'buscar'): array
    {
        $this->auditar($operacion, $rut, $motivo, $correlationId, $inicio, $totemId, null);
        return ['ok' => false, 'found' => false, 'disponible' => false, 'resultado' => $motivo, 'correlation_id' => $correlationId, 'message' => 'Servicio Personas temporalmente no disponible.'];
    }

    private function auditar(string $operacion, string $rut, string $resultado, string $correlationId, float $inicio, ?int $totemId, ?int $status): void
    {
        try {
            PersonaIntegracionAuditoria::create([
                'operacion' => $operacion, 'rut_hash' => $rut !== '' ? hash('sha256', $rut) : null,
                'resultado' => $resultado, 'http_status' => $status,
                'duracion_ms' => (int) round((microtime(true) - $inicio) * 1000),
                'correlation_id' => $correlationId, 'user_id' => auth()->id(),
                'totem_id' => $totemId, 'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function configurada(): bool
    {
        return trim((string) config('personas.base_url')) !== '' && trim((string) config('personas.token')) !== '';
    }

    public function normalizarRut(string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', trim($rut)) ?: '');
    }
}
