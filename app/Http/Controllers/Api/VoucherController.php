<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherAuditoria;
use App\Models\VoucherCobro;
use App\Models\VoucherPago;
use App\Models\VoucherPreconsulta;
use App\Services\VoucherQrPayloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function emitir(Request $request)
    {
        $request->validate([
            'preconsulta_id' => 'required|integer|exists:voucher_preconsultas,id',
            'preconsulta_token' => 'required|string|min:40',
            'cliente_id' => 'nullable|integer',
            'mascota_id' => 'nullable|integer',
            'criadero_cachorro_id' => 'nullable|integer',
            'cliente_rut' => 'nullable|string|max:30',
            'cliente_nombre' => 'nullable|string|max:150',
            'tipo_servicio' => 'nullable|string|max:150',
            'valor' => 'nullable|numeric|min:0',
            'copago_usuario' => 'nullable|numeric|min:0',
            'comision_veterchile' => 'nullable|numeric|min:0',
            'porcentaje_descuento' => 'nullable|numeric|min:0|max:100',
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($request) {
            $preconsulta = VoucherPreconsulta::with(['usuario', 'dependiente', 'servicio'])
                ->whereKey($request->preconsulta_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($preconsulta->resultado, ['autorizado', 'codigo_externo_recibido'], true)) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La preconsulta no está autorizada para generar voucher.',
                    'resultado_preconsulta' => $preconsulta->resultado,
                ], 422);
            }

            if (! $preconsulta->token_hash ||
                ! hash_equals($preconsulta->token_hash, hash('sha256', $request->preconsulta_token))) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Token de preconsulta inválido.',
                ], 422);
            }

            if ($preconsulta->token_expira_at && $preconsulta->token_expira_at->isPast()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Token de preconsulta vencido. Solicite una nueva validación.',
                ], 422);
            }

            if ($preconsulta->token_consumido_at) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Token de preconsulta ya utilizado.',
                ], 409);
            }

            if ($preconsulta->voucher_id) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La preconsulta ya fue usada para generar un voucher.',
                    'voucher_id' => $preconsulta->voucher_id,
                ], 409);
            }

            $valor = (float) ($request->valor ?? 0);
            if ($valor <= 0 && $preconsulta->servicio && $preconsulta->servicio->valor_referencial) {
                $valor = (float) $preconsulta->servicio->valor_referencial;
            }
            $copago = (float) ($request->copago_usuario ?? 0);
            $comision = (float) ($request->comision_veterchile ?? 0);
            $saldoVeterinario = max($valor - $copago - $comision, 0);

            $otp = random_int(100000, 999999);

            $voucher = Voucher::create([
                'codigo' => strtoupper(Str::random(10)),
                'qr_token' => (string) Str::uuid(),

                'cliente_id' => $request->cliente_id,
                'mascota_id' => $request->mascota_id,
                'criadero_cachorro_id' => $request->criadero_cachorro_id,

                'cliente_rut' => $request->cliente_rut,
                'cliente_nombre' => $request->cliente_nombre
                    ?: optional($preconsulta->dependiente)->nombre
                    ?: optional($preconsulta->usuario)->nombre,
                'tipo_servicio' => $request->tipo_servicio ?: optional($preconsulta->servicio)->nombre,

                'valor' => $valor,
                'copago_usuario' => $copago,
                'saldo_veterinario' => $saldoVeterinario,
                'comision_veterchile' => $comision,
                'porcentaje_descuento' => $request->porcentaje_descuento ?? 100,

                'estado' => $copago > 0 ? 'pendiente_pago' : 'activo',
                'fecha_vencimiento' => now()->addDays(30),

                'qr_expira' => now()->addDays(30),
                'qr_usado' => false,

                'otp_hash' => hash('sha256', (string) $otp),
                'otp_expira' => now()->addMinutes(10),
                'otp_validado_at' => null,
            ]);

            $firma = hash_hmac(
                'sha256',
                $voucher->id . $voucher->codigo,
                config('app.key')
            );

            $voucher->update([
                'qr_firma' => $firma,
            ]);

            $preconsulta->update([
                'voucher_id' => $voucher->id,
                'voucher_generado_codigo' => $voucher->codigo,
                'token_consumido_at' => now(),
            ]);

            VoucherPago::create([
                'voucher_id' => $voucher->id,
                'monto_pagado_usuario' => $copago,
                'metodo_pago' => $request->metodo_pago ?? 'pendiente',
                'estado_pago' => $copago > 0 ? 'pendiente' : 'pagado',
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_emitido',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Voucher emitido correctamente desde preconsulta '.$preconsulta->id,
                'ip' => request()->ip(),
            ]);

            $response = [
                'ok' => true,
                'voucher' => $voucher->fresh(),
                'qr_url' => url('/api/vouchers/'.$voucher->qr_token.'/validar'),
                'mensaje' => 'Voucher emitido correctamente',
            ];

            if (app()->environment(['local', 'testing'])) {
                $response['otp_demo'] = $otp;
            }

            return response()->json($response, 201);
        });
    }

    public function validar($qr_token, VoucherQrPayloadService $qrPayloadService)
    {
        $voucher = Voucher::where('qr_token', $qr_token)->first();

        if (!$voucher) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher no encontrado',
            ], 404);
        }

        $error = $this->validarDisponibilidadVoucher($voucher, false);

        if ($error) {
            return $error;
        }

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'voucher_validado',
            'usuario_tipo' => 'api',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Voucher validado correctamente',
            'ip' => request()->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'voucher' => [
                'codigo' => $voucher->codigo,
                'tipo_servicio' => $voucher->tipo_servicio,
                'estado' => $voucher->estado,
                'fecha_vencimiento' => $voucher->fecha_vencimiento,
                'prestador_nombre' => $voucher->prestador_nombre,
                'mascota_nombre' => $voucher->mascota_nombre,
            ],
            'qr_payload' => $qrPayloadService->build($voucher),
        ]);
    }

    public function canjear($qr_token)
    {
        return DB::transaction(function () use ($qr_token) {
            $voucher = Voucher::where('qr_token', $qr_token)
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Voucher no encontrado',
                ], 404);
            }

            $error = $this->validarDisponibilidadVoucher($voucher, false);

            if ($error) {
                return $error;
            }

            if ($voucher->estado !== 'validado_atencion' || $voucher->estado_validacion !== 'validada_por_asistente') {
                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'cobro_rechazado_sin_validacion_atencion',
                    'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                    'usuario_id' => auth()->id(),
                    'descripcion' => 'Intento de cobro antes del cierre profesional y validación del asistente.',
                    'ip' => request()->ip(),
                ]);

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El bono aún no está habilitado para cobro.',
                ], 409);
            }

            $voucher->update([
                'qr_usado' => true,
                'qr_usado_at' => now(),
                'estado' => 'cobrado',
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_cobrado',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Voucher cobrado correctamente y marcado como cobrado',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Voucher cobrado correctamente',
                'voucher_id' => $voucher->id,
                'codigo' => $voucher->codigo,
                'estado' => $voucher->estado,
                'qr_usado_at' => $voucher->qr_usado_at,
            ]);
        });
    }

    public function cobrar(Request $request, $qr_token)
    {
        $request->validate([
            'veterinario_id' => 'nullable|integer',
            'veterinario_nombre' => 'nullable|string|max:150',
            'sucursal' => 'nullable|string|max:150',
        ]);

        return DB::transaction(function () use ($request, $qr_token) {
            $voucher = Voucher::where('qr_token', $qr_token)
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Voucher no encontrado',
                ], 404);
            }

            $error = $this->validarDisponibilidadVoucher($voucher, true);

            if ($error) {
                return $error;
            }

            if (!$voucher->otp_validado_at) {
                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'cobro_rechazado_sin_otp',
                    'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                    'usuario_id' => auth()->id(),
                    'descripcion' => 'Intento de cobro sin validación OTP',
                    'ip' => request()->ip(),
                ]);

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El cliente aún no ha validado el OTP',
                ], 400);
            }

            $voucher->update([
                'estado' => 'usado',
                'qr_usado' => true,
                'qr_usado_at' => now(),
                'usado_en' => now(),
            ]);

            $cobro = VoucherCobro::firstOrCreate(['voucher_id' => $voucher->id], [
                'voucher_id' => $voucher->id,
                'profesional_id' => $voucher->profesional_id,
                'veterinario_id' => $request->veterinario_id,
                'veterinario_nombre' => $request->veterinario_nombre,
                'sucursal' => $request->sucursal,
                'monto_cobrado' => $voucher->saldo_veterinario,
                'estado' => 'pendiente_rendicion',
                'cobrado_en' => now(),
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_usado',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Voucher usado y cobro creado',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Voucher cobrado correctamente',
                'voucher' => $voucher->fresh(),
                'cobro' => $cobro,
            ]);
        });
    }

    public function marcarPagado($qr_token)
    {
        $voucher = Voucher::where('qr_token', $qr_token)->firstOrFail();

        $pago = $voucher->pagos()->latest()->first();

        if (!$pago) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Este voucher no tiene pago asociado',
            ], 400);
        }

        $pago->update([
            'estado_pago' => 'pagado',
            'metodo_pago' => 'demo',
        ]);

        $voucher->update([
            'estado' => 'activo',
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'pago_confirmado',
            'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Pago confirmado y voucher activado',
            'ip' => request()->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Pago confirmado y voucher activado',
            'voucher' => $voucher->fresh(),
        ]);
    }

    public function validarOtp(Request $request, $qr_token)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $voucher = Voucher::where('qr_token', $qr_token)->first();

        if (!$voucher) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher no encontrado',
            ], 404);
        }

        if ($voucher->otp_validado_at) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'OTP ya fue validado',
            ], 400);
        }

        if (!$voucher->otp_expira || now()->gt($voucher->otp_expira)) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'otp_expirado',
                'usuario_tipo' => 'cliente',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Cliente intentó validar OTP expirado',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'OTP expirado',
            ], 400);
        }

        $otpHash = hash('sha256', (string) $request->otp);

        if (!hash_equals((string) $voucher->otp_hash, $otpHash)) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'otp_incorrecto',
                'usuario_tipo' => 'cliente',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Cliente ingresó OTP incorrecto',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'OTP incorrecto',
            ], 400);
        }

        $voucher->update([
            'otp_validado_at' => now(),
            'estado' => 'activo',
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'otp_validado_cliente',
            'usuario_tipo' => 'cliente',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Cliente validó OTP y activó voucher',
            'ip' => request()->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Voucher activado correctamente',
            'estado' => $voucher->fresh()->estado,
        ]);
    }

    private function validarDisponibilidadVoucher(Voucher $voucher, bool $requiereActivo = true)
    {
        if ($voucher->estado === 'invalidado_cliente') {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'intento_validar_voucher_invalidado',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Intento de validar voucher invalidado por cliente',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Este voucher fue invalidado por el cliente',
            ], 400);
        }

        $firmaCalculada = hash_hmac(
            'sha256',
            $voucher->id . $voucher->codigo,
            config('app.key')
        );

        if (!$voucher->qr_firma || !hash_equals((string) $voucher->qr_firma, $firmaCalculada)) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'firma_invalida',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Intento de validación con firma alterada',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher inválido',
            ], 403);
        }

        if ($voucher->qr_expira && now()->gt($voucher->qr_expira)) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_expirado',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Intento de uso de voucher expirado',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher expirado',
            ], 400);
        }

        if ($voucher->qr_usado) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_reutilizado',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Intento de reutilización de voucher',
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher ya utilizado',
            ], 400);
        }

        if ($requiereActivo && $voucher->estado !== 'activo') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Voucher no disponible',
            ], 400);
        }

        return null;
    }
}
