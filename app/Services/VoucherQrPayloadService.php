<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherBaseUsuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class VoucherQrPayloadService
{
    public function build(Voucher $voucher): array
    {
        $voucher->loadMissing(['servicio']);

        $rutTitular = $voucher->cliente_rut_visible;
        $baseUsuario = $this->baseUsuario($rutTitular);

        $payload = [
            'version' => 'SDI-QR-1',
            'qr_url' => route('vouchers.qr', $voucher->qr_token),
            'validacion_api_url' => url('/api/vouchers/'.$voucher->qr_token.'/validar'),
            'emitido_en' => optional($voucher->created_at)->toIso8601String(),
            'voucher' => [
                'id' => $voucher->id,
                'codigo' => $voucher->codigo,
                'estado' => $voucher->estado,
                'servicio' => $voucher->tipo_servicio ?: optional($voucher->servicio)->nombre,
                'valor' => (float) $voucher->valor,
                'copago' => (float) $voucher->copago_usuario,
                'vence' => $this->dateValue($voucher->fecha_vencimiento),
            ],
            'profesional' => [
                'id' => $voucher->profesional_id,
                'nombre' => $voucher->prestador_nombre,
                'rut' => $voucher->prestador_rut,
                'especialidad' => $voucher->prestador_especialidad,
                'email' => $voucher->prestador_email,
                'telefono' => $voucher->prestador_telefono,
                'direccion' => $voucher->prestador_direccion,
            ],
            'titular' => [
                'nombre' => $voucher->cliente_nombre ?: optional($baseUsuario)->nombre,
                'rut' => $rutTitular,
                'grupo_ingreso' => data_get(optional($baseUsuario)->otros, 'grupo_ingreso'),
                'telefono' => $voucher->cliente_telefono,
                'email' => $voucher->cliente_email,
                'direccion' => $this->decrypt(optional($baseUsuario)->direccion_encrypted),
                'fecha_nacimiento' => $this->decrypt(optional($baseUsuario)->fecha_nacimiento_encrypted),
                'edad' => $this->edad($this->decrypt(optional($baseUsuario)->fecha_nacimiento_encrypted)),
            ],
            'beneficiarios' => $this->beneficiarios($baseUsuario),
            'beneficiario_seleccionado' => $this->beneficiarioSeleccionado($voucher, $baseUsuario, $rutTitular),
        ];

        $payload['integridad'] = [
            'algoritmo' => 'HMAC-SHA256',
            'firma' => hash_hmac(
                'sha256',
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                (string) config('app.key')
            ),
        ];

        return $payload;
    }

    public function compactText(Voucher $voucher): string
    {
        $payload = $this->build($voucher);
        $titular = $payload['titular'];

        $lines = [
            'SDI BONO/VOUCHER',
            'Código: '.$payload['voucher']['codigo'],
            'Estado: '.$payload['voucher']['estado'],
            'Servicio: '.$payload['voucher']['servicio'],
            'Titular: '.($titular['nombre'] ?: 'Sin nombre'),
            'RUT titular: '.($titular['rut'] ?: 'Sin RUT'),
            'Dirección: '.($titular['direccion'] ?: 'Sin dirección'),
            'Edad: '.($titular['edad'] !== null ? $titular['edad'].' años' : 'Sin edad'),
        ];

        foreach ($payload['beneficiarios'] as $beneficiario) {
            $lines[] = 'Beneficiario: '.$beneficiario['nombre']
                .' | RUT: '.($beneficiario['rut'] ?: 'Sin RUT')
                .' | Dirección: '.($beneficiario['direccion'] ?: 'Sin dirección')
                .' | Edad: '.($beneficiario['edad'] !== null ? $beneficiario['edad'].' años' : 'Sin edad');
        }

        if (! empty($payload['beneficiario_seleccionado'])) {
            $seleccionado = $payload['beneficiario_seleccionado'];
            $lines[] = 'Voucher para: '.($seleccionado['nombre'] ?: 'Sin nombre')
                .' | RUT: '.($seleccionado['rut'] ?: 'Sin RUT')
                .' | Tipo: '.($seleccionado['parentesco'] ?: 'Titular')
                .' | Dirección: '.($seleccionado['direccion'] ?: 'Sin dirección')
                .' | Edad: '.($seleccionado['edad'] !== null ? $seleccionado['edad'].' años' : 'Sin edad');
        }

        if (! empty($payload['profesional']['nombre'])) {
            $lines[] = 'Profesional: '.$payload['profesional']['nombre']
                .' | RUT: '.($payload['profesional']['rut'] ?: 'Sin RUT')
                .' | Especialidad: '.($payload['profesional']['especialidad'] ?: 'Sin especialidad');
        }

        $lines[] = 'Token: '.$voucher->qr_token;
        $lines[] = 'Firma: '.$payload['integridad']['firma'];

        return implode("\n", $lines);
    }

    private function baseUsuario(?string $rut): ?VoucherBaseUsuario
    {
        if (! $rut) {
            return null;
        }

        return VoucherBaseUsuario::with(['dependientes' => function ($query) {
            $query->where('estado', 'activo')->orderBy('nombre');
        }])
            ->where('rut_hash', $this->rutHmac($rut))
            ->orWhere('rut_sha256', $this->rutSha256($rut))
            ->first();
    }

    private function beneficiarios(?VoucherBaseUsuario $baseUsuario): array
    {
        if (! $baseUsuario) {
            return [];
        }

        return $baseUsuario->dependientes
            ->map(function ($dependiente) {
                $fechaNacimiento = $this->decrypt($dependiente->fecha_nacimiento_encrypted);

                return [
                    'nombre' => $dependiente->nombre,
                    'rut' => $this->decrypt($dependiente->rut_encrypted),
                    'parentesco' => $dependiente->parentesco,
                    'direccion' => $this->decrypt($dependiente->direccion_encrypted),
                    'fecha_nacimiento' => $fechaNacimiento,
                    'edad' => $this->edad($fechaNacimiento),
                    'estado' => $dependiente->estado,
                ];
            })
            ->values()
            ->all();
    }

    private function beneficiarioSeleccionado(Voucher $voucher, ?VoucherBaseUsuario $baseUsuario, ?string $rutTitular): array
    {
        $fechaNacimiento = $this->decrypt($voucher->beneficiario_fecha_nacimiento);

        if ($voucher->beneficiario_nombre || $voucher->beneficiario_rut) {
            return [
                'tipo' => $voucher->beneficiario_tipo ?: 'titular',
                'nombre' => $voucher->beneficiario_nombre ?: $voucher->cliente_nombre,
                'rut' => $this->decrypt($voucher->beneficiario_rut) ?: $rutTitular,
                'parentesco' => $voucher->beneficiario_parentesco ?: 'Titular',
                'grupo_ingreso' => data_get(optional($baseUsuario)->otros, 'grupo_ingreso'),
                'direccion' => $this->decrypt($voucher->beneficiario_direccion),
                'fecha_nacimiento' => $fechaNacimiento,
                'edad' => $voucher->beneficiario_edad ?? $this->edad($fechaNacimiento),
            ];
        }

        $titularFechaNacimiento = $this->decrypt(optional($baseUsuario)->fecha_nacimiento_encrypted);

        return [
            'tipo' => 'titular',
            'nombre' => $voucher->cliente_nombre ?: optional($baseUsuario)->nombre,
            'rut' => $rutTitular,
            'parentesco' => 'Titular',
            'grupo_ingreso' => data_get(optional($baseUsuario)->otros, 'grupo_ingreso'),
            'direccion' => $this->decrypt(optional($baseUsuario)->direccion_encrypted),
            'fecha_nacimiento' => $titularFechaNacimiento,
            'edad' => $this->edad($titularFechaNacimiento),
        ];
    }

    private function decrypt($value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    }

    private function edad(?string $fecha): ?int
    {
        if (! filled($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->age;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function dateValue($value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function normalizarRut($rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }

    private function rutHmac($rut): string
    {
        return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key'));
    }

    private function rutSha256($rut): string
    {
        return hash('sha256', $this->normalizarRut($rut));
    }
}
