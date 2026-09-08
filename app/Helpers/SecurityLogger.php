<?php

namespace App\Helpers;

use App\Models\SecurityLog;

class SecurityLogger
{
    public static function log(
        string $accion,
        ?string $modelo = null,
        ?int $modeloId = null,
        ?string $estado = 'ok',
        ?string $detalle = null,
        ?int $clienteId = null
    ) {
        try {
            return SecurityLog::create([
                'user_id' => auth()->id(),
                'cliente_id' => $clienteId,
                'accion' => $accion,
                'modelo' => $modelo,
                'modelo_id' => $modeloId,
                'ip' => request()->ip(),
                'device' => request()->userAgent(),
                'estado' => $estado,
                'detalle' => $detalle,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
