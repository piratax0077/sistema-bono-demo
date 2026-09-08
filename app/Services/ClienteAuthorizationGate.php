<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteAutorizacion;
use App\Models\ClienteDispositivo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteAuthorizationGate
{
    public const TOKEN_INPUTS = [
        'cliente_authorization_token',
        'cliente_autorizacion_token',
        'authorization_token',
        'autorizacion_token',
    ];

    public function clienteIdForUser(User $user): ?int
    {
        if (! $user->rut) {
            return null;
        }

        return $this->clienteIdForRut($user->rut, $user->id);
    }

    public function clienteIdForRut(?string $rut, ?int $fallbackUserId = null): ?int
    {
        $rutNormalizado = $this->normalizarRut($rut);

        if ($rutNormalizado !== '') {
            $cliente = Cliente::where('rut_hash', hash('sha256', $rutNormalizado))->first();

            if ($cliente) {
                return $cliente->id;
            }
        }

        if ($fallbackUserId) {
            $user = User::find($fallbackUserId);

            if ($user && $user->rut) {
                $rutNormalizado = $this->normalizarRut($user->rut);
                $cliente = Cliente::where('rut_hash', hash('sha256', $rutNormalizado))->first();

                if ($cliente) {
                    return $cliente->id;
                }
            }
        }

        return null;
    }

    public function tokenFrom(Request $request): ?string
    {
        foreach (self::TOKEN_INPUTS as $input) {
            $token = trim((string) $request->input($input));

            if ($token !== '') {
                return $token;
            }
        }

        return null;
    }

    public function requestAuthorization(
        int $clienteId,
        string $tipoAccion,
        ?string $referenciaTipo,
        ?int $referenciaId,
        Request $request,
        array $metadata = []
    ): array {
        $dispositivo = ClienteDispositivo::where('cliente_id', $clienteId)
            ->where('estado', 'activo')
            ->orderBy('id')
            ->first();

        if (! $dispositivo) {
            return [
                'ok' => false,
                'estado' => 'sin_dispositivo',
                'mensaje' => 'El beneficiario no tiene una app/dispositivo autorizador activo.',
            ];
        }

        $autorizacion = ClienteAutorizacion::create([
            'cliente_id' => $clienteId,
            'dispositivo_id' => $dispositivo->id,
            'tipo_accion' => $tipoAccion,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
            'token' => Str::random(80),
            'estado' => 'pendiente',
            'ip_solicitante' => $request->ip(),
            'metadata' => $metadata,
            'expira_at' => now()->addMinutes(5),
        ]);

        return [
            'ok' => false,
            'estado' => 'pendiente',
            'mensaje' => 'Solicitud enviada a la app autorizadora del beneficiario.',
            'autorizacion' => $autorizacion,
        ];
    }

    public function verifyApproved(
        ?string $token,
        int $clienteId,
        ?string $tipoAccion = null
    ): array {
        if (! $token) {
            return [
                'ok' => false,
                'estado' => 'sin_token',
                'mensaje' => 'Falta el token de autorizacion del beneficiario.',
            ];
        }

        $query = ClienteAutorizacion::where('token', $token)
            ->where('cliente_id', $clienteId);

        if ($tipoAccion) {
            $query->where('tipo_accion', $tipoAccion);
        }

        $autorizacion = $query->first();

        if (! $autorizacion) {
            return [
                'ok' => false,
                'estado' => 'no_encontrada',
                'mensaje' => 'Autorizacion del beneficiario no encontrada.',
            ];
        }

        if ($autorizacion->estado === 'pendiente' &&
            $autorizacion->expira_at &&
            now()->gt($autorizacion->expira_at)) {
            $autorizacion->update(['estado' => 'expirada']);
            $autorizacion = $autorizacion->fresh();
        }

        if ($autorizacion->estado !== 'aprobada') {
            return [
                'ok' => false,
                'estado' => $autorizacion->estado,
                'mensaje' => $this->estadoMensaje($autorizacion->estado),
                'autorizacion' => $autorizacion,
            ];
        }

        return [
            'ok' => true,
            'estado' => 'aprobada',
            'mensaje' => 'Autorizacion aprobada por la app del beneficiario.',
            'autorizacion' => $autorizacion,
        ];
    }

    public function ensureApprovedOrRequest(
        Request $request,
        int $clienteId,
        string $tipoAccion,
        ?string $referenciaTipo,
        ?int $referenciaId,
        array $metadata = []
    ): array {
        $token = $this->tokenFrom($request);

        if ($token) {
            return $this->verifyApproved($token, $clienteId, $tipoAccion);
        }

        return $this->requestAuthorization(
            $clienteId,
            $tipoAccion,
            $referenciaTipo,
            $referenciaId,
            $request,
            $metadata
        );
    }

    public function authorizationPayload(ClienteAutorizacion $autorizacion): array
    {
        return [
            'id' => $autorizacion->id,
            'token' => $autorizacion->token,
            'estado' => $autorizacion->estado,
            'tipo_accion' => $autorizacion->tipo_accion,
            'referencia_tipo' => $autorizacion->referencia_tipo,
            'referencia_id' => $autorizacion->referencia_id,
            'expira_at' => optional($autorizacion->expira_at)->toIso8601String(),
            'metadata' => $autorizacion->metadata ?: [],
        ];
    }

    public function normalizarRut(?string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }

    private function estadoMensaje(string $estado): string
    {
        return [
            'pendiente' => 'La compra esta esperando aprobacion en la app del beneficiario.',
            'rechazada' => 'La compra fue rechazada por el beneficiario.',
            'expirada' => 'La autorizacion del beneficiario expiro; solicite una nueva.',
            'sin_dispositivo' => 'El beneficiario no tiene app autorizadora activa.',
            'no_encontrada' => 'Autorizacion no encontrada.',
        ][$estado] ?? 'La autorizacion del beneficiario no esta aprobada.';
    }
}
