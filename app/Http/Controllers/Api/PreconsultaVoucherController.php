<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditorNotificacion;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseDependiente;
use App\Models\VoucherBaseLaboratorio;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherPreconsulta;
use App\Models\VoucherPreconsultaAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreconsultaVoucherController extends Controller
{
    public function validar(Request $request)
    {
        $data = $request->validate([
            'external_consulta_id' => 'nullable|string|max:100',
            'usuario_rut' => 'required|string|max:20',
            'dependiente_rut' => 'nullable|string|max:20',
            'prestador_tipo' => 'nullable|in:profesional,laboratorio,servicio',
            'profesional_rut' => 'nullable|string|max:20',
            'laboratorio_rut' => 'nullable|string|max:20',
            'servicio_codigo' => 'nullable|string|max:80',
            'geolocalizacion_lat' => 'nullable|numeric|between:-90,90',
            'geolocalizacion_lng' => 'nullable|numeric|between:-180,180',
            'codigo_voucher_externo' => 'nullable|string|max:100',
            'guardar_detalle' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $contradicciones = [];
            $porques = [];

            $usuarioHash = $this->rutHash($data['usuario_rut']);
            $usuarioSha256 = $this->rutSha256($data['usuario_rut']);
            $profesionalHash = ! empty($data['profesional_rut']) ? $this->rutHash($data['profesional_rut']) : null;
            $profesionalSha256 = ! empty($data['profesional_rut']) ? $this->rutSha256($data['profesional_rut']) : null;
            $laboratorioHash = ! empty($data['laboratorio_rut']) ? $this->rutHash($data['laboratorio_rut']) : null;
            $laboratorioSha256 = ! empty($data['laboratorio_rut']) ? $this->rutSha256($data['laboratorio_rut']) : null;
            $servicioCodigo = strtoupper(trim($data['servicio_codigo'] ?? ''));
            $sinDestino = empty($data['profesional_rut'])
                && empty($data['laboratorio_rut'])
                && empty($data['servicio_codigo']);
            $guardarDetalle = (bool) ($data['guardar_detalle'] ?? true);
            $hashModo = 'hmac_sha256';

            $usuario = null;
            $dependiente = null;
            $profesional = null;
            $laboratorio = null;
            $servicio = null;
            $relacion = null;
            $resultado = 'rechazado';
            $motivo = null;

            if ($sinDestino) {
                $this->agregarContradiccion(
                    $contradicciones,
                    'destino_no_informado',
                    'La base externa no envio profesional, laboratorio ni servicio.',
                    'No se puede iniciar una autorizacion segura sin destino del bono.'
                );
                $this->agregarPorque(
                    $porques,
                    'destino_obligatorio',
                    'El primer candado del flujo exige relacion usuario/dependiente contra profesional, laboratorio o servicio antes de generar bono.'
                );
            }

            [$usuario, $usuarioHashModo] = $this->buscarPorRutSeguro(
                new VoucherBaseUsuario,
                $usuarioHash,
                $usuarioSha256
            );
            $hashModo = $this->combinarModoHash($hashModo, $usuarioHashModo);

            if (! $usuario) {
                $motivo = 'Usuario no existe o no esta vigente en base externa.';
                $this->agregarContradiccion(
                    $contradicciones,
                    'usuario_no_vigente',
                    'El RUT consultado no coincide con un usuario vigente de la base externa.',
                    'No se autoriza generar bono para usuarios no confirmados por la base origen.'
                );
                $this->agregarPorque(
                    $porques,
                    'usuario_base_origen',
                    'El sistema de vouchers no crea la relacion por si solo; solo acepta relaciones vigentes enviadas desde la base externa.'
                );
            } elseif (! empty($data['dependiente_rut'])) {
                [$dependiente, $dependienteHashModo] = $this->buscarPorRutSeguro(
                    (new VoucherBaseDependiente)->where('usuario_id', $usuario->id),
                    $this->rutHash($data['dependiente_rut']),
                    $this->rutSha256($data['dependiente_rut'])
                );
                $hashModo = $this->combinarModoHash($hashModo, $dependienteHashModo);

                if (! $dependiente) {
                    $motivo = 'Carga/dependiente no existe, no depende del usuario o no esta vigente.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'dependiente_no_relacionado',
                        'Se informo dependiente, pero no existe una carga vigente asociada al usuario.',
                        'Evita que un bono sea emitido para una persona no autorizada por el titular.'
                    );
                    $this->agregarPorque(
                        $porques,
                        'dependiente_debe_depender_del_titular',
                        'Cuando se informa dependiente, la base externa debe confirmar que la carga pertenece al titular vigente.'
                    );
                }
            }

            if ($usuario && ! $motivo && $sinDestino) {
                $motivo = 'Debe venir profesional, laboratorio o servicio desde la base externa.';
            }

            if ($usuario && ! $motivo && $profesionalHash) {
                [$profesional, $profesionalHashModo] = $this->buscarPorRutSeguro(
                    new VoucherBaseProfesional,
                    $profesionalHash,
                    $profesionalSha256
                );
                $hashModo = $this->combinarModoHash($hashModo, $profesionalHashModo);

                if (! $profesional) {
                    $motivo = 'Profesional no existe o no esta vigente en base externa.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'profesional_no_vigente',
                        'El profesional informado no esta registrado como vigente en la base externa.',
                        'No se permite generar bono a un prestador no habilitado.'
                    );
                    $this->agregarPorque(
                        $porques,
                        'prestador_vigente_obligatorio',
                        'La autorizacion depende de un prestador activo y validado por la base origen.'
                    );
                }
            }

            if ($usuario && ! $motivo && $laboratorioHash) {
                [$laboratorio, $laboratorioHashModo] = $this->buscarPorRutSeguro(
                    new VoucherBaseLaboratorio,
                    $laboratorioHash,
                    $laboratorioSha256
                );
                $hashModo = $this->combinarModoHash($hashModo, $laboratorioHashModo);

                if (! $laboratorio) {
                    $motivo = 'Laboratorio no existe o no esta vigente en base externa.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'laboratorio_no_vigente',
                        'El laboratorio informado no esta registrado como vigente en la base externa.',
                        'No se permite generar bono a una institucion no habilitada.'
                    );
                    $this->agregarPorque(
                        $porques,
                        'institucion_vigente_obligatoria',
                        'La autorizacion depende de una institucion activa y validada por la base origen.'
                    );
                }
            }

            if ($usuario && ! $motivo && $servicioCodigo !== '') {
                $servicio = $this->vigente(
                    VoucherBaseServicio::where('codigo', $servicioCodigo)
                )->first();

                if (! $servicio) {
                    $motivo = 'Servicio no existe o no esta vigente en base externa.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'servicio_no_vigente',
                        'El codigo de servicio informado no esta vigente en la base externa.',
                        'No se permite emitir un bono para prestaciones no autorizadas.'
                    );
                    $this->agregarPorque(
                        $porques,
                        'servicio_vigente_obligatorio',
                        'El nivel de bono y la prestacion deben venir confirmados por la base externa antes de la venta.'
                    );
                }
            }

            if ($usuario && ! $motivo) {
                $relacion = $this->buscarRelacionVigente($usuario, $dependiente, $profesional, $laboratorio, $servicio);

                if (! $relacion) {
                    $motivo = 'No existe relacion vigente usuario/dependiente con profesional, laboratorio o servicio.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'relacion_no_encontrada',
                        'La combinacion enviada no tiene una relacion vigente en la base externa.',
                        'Bloquea cruces no autorizados entre paciente, prestador y servicio.',
                        [
                            'usuario_id' => $usuario->id,
                            'dependiente_id' => $dependiente ? $dependiente->id : null,
                            'profesional_id' => $profesional ? $profesional->id : null,
                            'laboratorio_id' => $laboratorio ? $laboratorio->id : null,
                            'servicio_id' => $servicio ? $servicio->id : null,
                        ]
                    );
                    $this->agregarPorque(
                        $porques,
                        'relacion_previa_es_el_permiso',
                        'La relacion previa es la prueba de que ese usuario o carga puede comprar ese bono para ese destino.'
                    );
                } elseif ($relacion->requiere_auditoria) {
                    $resultado = 'auditoria';
                    $motivo = $relacion->motivo_auditoria ?: 'Relacion vigente marcada para auditoria previa.';
                    $this->agregarContradiccion(
                        $contradicciones,
                        'relacion_marcada_auditoria',
                        'La relacion existe, pero la base externa la marco para revision humana.',
                        'El auditor debe autorizar antes de continuar el flujo.',
                        ['relacion_id' => $relacion->id]
                    );
                    $this->agregarPorque(
                        $porques,
                        'auditoria_previa_definida_por_base',
                        'Aunque existe relacion, la regla de negocio exige que Contraloria/Auditoria revise antes de liberar el bono.'
                    );
                } elseif (! empty($data['codigo_voucher_externo'])) {
                    $resultado = 'codigo_externo_recibido';
                    $motivo = 'Base externa autorizo y envio codigo de voucher.';
                    $guardarDetalle = false;
                } else {
                    $resultado = 'autorizado';
                    $motivo = 'Relacion vigente validada en base externa.';
                }
            }

            if (in_array($resultado, ['autorizado', 'codigo_externo_recibido'], true) && $hashModo !== 'hmac_sha256') {
                $resultado = 'auditoria';
                $motivo = 'La validacion de identidad no completo el doble control HMAC + SHA-256.';
                $this->agregarContradiccion(
                    $contradicciones,
                    'doble_hash_no_confirmado',
                    'La coincidencia no paso con ambos candados criptograficos esperados.',
                    'Se evita emitir bono cuando falta una de las dos pruebas de identidad.',
                    ['hash_validacion_modo' => $hashModo]
                );
                $this->agregarPorque(
                    $porques,
                    'hmac_y_sha256_como_escollo',
                    'HMAC protege contra lectura directa y SHA-256 actua como contraste adicional; si no calzan ambos, pasa a auditoria.'
                );
            }

            $rechazosPrevios = $this->rechazosRecientes($usuarioHash);
            if ($resultado === 'rechazado' && $usuarioHash && $rechazosPrevios >= 9) {
                $resultado = 'auditoria';
                $motivo = 'Decimo rechazo reciente para el mismo usuario. Requiere auditoria.';
                $this->agregarContradiccion(
                    $contradicciones,
                    'umbral_rechazos',
                    'El usuario acumula diez intentos rechazados o enviados a auditoria en los ultimos 7 dias.',
                    'El patron puede indicar error de integracion, fuerza bruta o uso indebido.'
                );
                $this->agregarPorque(
                    $porques,
                    'proteccion_por_intentos',
                    'Al superar el umbral, se detiene la automatizacion para que un auditor revise el patron antes de nuevas emisiones.'
                );
            }

            if (in_array($resultado, ['rechazado', 'auditoria'], true) && empty($porques)) {
                $this->agregarPorque(
                    $porques,
                    'falla_bloquea_emision',
                    'La emision solo se habilita cuando la base externa confirma usuario, destino, servicio y relacion vigente.'
                );
            }

            $intentosFallidosPrevios = $this->intentosFallidosRecientes($usuarioHash);
            $intentosFallidosCount = $rechazosPrevios
                + (in_array($resultado, ['rechazado', 'auditoria'], true) ? 1 : 0);

            $tokenPreconsulta = in_array($resultado, ['autorizado', 'codigo_externo_recibido'], true)
                ? rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=')
                : null;
            $tokenExpiraAt = $tokenPreconsulta
                ? now()->addMinutes((int) config('voucher_preconsulta.token_minutes', 15))
                : null;

            $responsePayload = [
                'resultado' => $resultado,
                'motivo' => $motivo,
                'relacion_autorizada_id' => $relacion ? $relacion->id : null,
                'hash_validacion_modo' => $hashModo,
                'contradicciones' => $contradicciones,
                'intentos_fallidos_count' => $intentosFallidosCount,
                'porques' => $porques,
            ];

            $preconsulta = VoucherPreconsulta::create([
                'external_consulta_id' => $data['external_consulta_id'] ?? null,
                'usuario_id' => $usuario ? $usuario->id : null,
                'dependiente_id' => $dependiente ? $dependiente->id : null,
                'profesional_id' => $profesional ? $profesional->id : null,
                'laboratorio_id' => $laboratorio ? $laboratorio->id : null,
                'servicio_id' => $servicio ? $servicio->id : null,
                'relacion_autorizada_id' => $relacion ? $relacion->id : null,
                'usuario_consulta_tipo' => $dependiente ? 'dependiente' : 'titular',
                'usuario_rut_hash' => $usuarioHash,
                'usuario_rut_sha256' => $usuarioSha256,
                'prestador_tipo' => $this->prestadorTipo($data, $profesional, $laboratorio, $servicio),
                'prestador_rut_hash' => $profesionalHash ?: $laboratorioHash,
                'prestador_rut_sha256' => $profesionalSha256 ?: $laboratorioSha256,
                'hash_validacion_modo' => $hashModo,
                'geolocalizacion_lat' => $data['geolocalizacion_lat'] ?? null,
                'geolocalizacion_lng' => $data['geolocalizacion_lng'] ?? null,
                'fecha_consulta' => now(),
                'hora_respuesta' => now(),
                'token_hash' => $tokenPreconsulta ? hash('sha256', $tokenPreconsulta) : null,
                'token_expira_at' => $tokenExpiraAt,
                'resultado' => $resultado,
                'motivo' => $motivo,
                'codigo_voucher_externo' => $data['codigo_voucher_externo'] ?? null,
                'guardar_detalle' => $guardarDetalle,
                'request_fingerprint' => hash('sha256', json_encode([
                    $usuarioHash,
                    $profesionalHash,
                    $laboratorioHash,
                    $servicioCodigo,
                    $data['external_consulta_id'] ?? null,
                ])),
                'request_payload' => $guardarDetalle ? $this->sanitizedPayload($data) : null,
                'response_payload' => $responsePayload,
                'ip' => $request->ip(),
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
            ]);

            $intentosFallidos = $this->agregarIntentoActual(
                $intentosFallidosPrevios,
                $preconsulta,
                $resultado,
                $motivo
            );

            if ($resultado === 'auditoria') {
                $this->notificarAuditoria(
                    $preconsulta,
                    $motivo,
                    $contradicciones,
                    $intentosFallidosCount,
                    $intentosFallidos,
                    $porques,
                    $this->contextoAuditoria($request, $data, $hashModo, $relacion)
                );
            }

            return response()->json([
                'data' => [
                    'preconsulta_id' => $preconsulta->id,
                    'preconsulta_token' => $tokenPreconsulta,
                    'preconsulta_token_expires_at' => $tokenExpiraAt ? $tokenExpiraAt->toIso8601String() : null,
                    'preconsulta_token_minutes' => $tokenPreconsulta
                        ? (int) config('voucher_preconsulta.token_minutes', 15)
                        : null,
                    'resultado' => $resultado,
                    'can_generate_voucher' => in_array($resultado, ['autorizado', 'codigo_externo_recibido'], true),
                    'requires_audit' => $resultado === 'auditoria',
                    'hash_validation_mode' => $hashModo,
                    'motivo' => $motivo,
                    'contradicciones' => $contradicciones,
                    'intentos_fallidos_count' => $intentosFallidosCount,
                    'porques' => $porques,
                    'relacion_autorizada_id' => $relacion ? $relacion->id : null,
                    'usuario' => $usuario ? ['id' => $usuario->id, 'nombre' => $usuario->nombre] : null,
                    'dependiente' => $dependiente ? ['id' => $dependiente->id, 'nombre' => $dependiente->nombre] : null,
                    'prestador' => $this->prestadorResumen($profesional, $laboratorio),
                    'servicio' => $servicio ? [
                        'id' => $servicio->id,
                        'codigo' => $servicio->codigo,
                        'nombre' => $servicio->nombre,
                        'nivel_bono' => $servicio->nivel_bono,
                    ] : null,
                    'codigo_voucher_externo' => $data['codigo_voucher_externo'] ?? null,
                ],
            ], $resultado === 'rechazado' ? 422 : 200);
        });
    }

    private function buscarRelacionVigente($usuario, $dependiente, $profesional, $laboratorio, $servicio)
    {
        $query = VoucherBaseRelacion::where('usuario_id', $usuario->id);

        if ($dependiente) {
            $query->where('dependiente_id', $dependiente->id);
        } else {
            $query->whereNull('dependiente_id');
        }

        if ($profesional) {
            $query->where('profesional_id', $profesional->id);
        }

        if ($laboratorio) {
            $query->where('laboratorio_id', $laboratorio->id);
        }

        if ($servicio) {
            $query->where('servicio_id', $servicio->id);
        }

        return $this->vigente($query->where('estado', 'vigente'))->first();
    }

    private function vigente($query)
    {
        return $query
            ->whereIn('estado', ['activo', 'vigente'])
            ->where(function ($builder) {
                $builder->whereNull('vigente_desde')
                    ->orWhere('vigente_desde', '<=', now()->toDateString());
            })
            ->where(function ($builder) {
                $builder->whereNull('vigente_hasta')
                    ->orWhere('vigente_hasta', '>=', now()->toDateString());
            });
    }

    private function buscarPorRutSeguro($source, $hmac, $sha256)
    {
        $query = $source instanceof \Illuminate\Database\Eloquent\Model
            ? $source->newQuery()
            : $source;

        $doble = $this->vigente((clone $query)
            ->where('rut_hash', $hmac)
            ->where('rut_sha256', $sha256))
            ->first();

        if ($doble) {
            return [$doble, 'hmac_sha256'];
        }

        $soloHmac = $this->vigente((clone $query)
            ->where('rut_hash', $hmac)
            ->where(function ($builder) {
                $builder->whereNull('rut_sha256')
                    ->orWhere('rut_sha256', '');
            }))
            ->first();

        if ($soloHmac) {
            return [$soloHmac, 'hmac_only'];
        }

        $soloSha256 = $this->vigente((clone $query)
            ->where('rut_sha256', $sha256)
            ->where(function ($builder) {
                $builder->whereNull('rut_hash')
                    ->orWhere('rut_hash', '');
            }))
            ->first();

        if ($soloSha256) {
            return [$soloSha256, 'legacy_sha256'];
        }

        return [null, 'no_match'];
    }

    private function combinarModoHash($actual, $nuevo)
    {
        if ($nuevo === 'legacy_sha256' || $actual === 'legacy_sha256') {
            return 'legacy_sha256';
        }

        if ($nuevo === 'hmac_only' || $actual === 'hmac_only') {
            return 'hmac_only';
        }

        if ($nuevo === 'no_match' && $actual === 'hmac_sha256') {
            return 'no_match';
        }

        if ($actual === 'no_match' && $nuevo === 'hmac_sha256') {
            return 'hmac_sha256';
        }

        return $actual ?: $nuevo;
    }

    private function rechazosRecientes($usuarioHash)
    {
        if (! $usuarioHash) {
            return 0;
        }

        return VoucherPreconsulta::where('usuario_rut_hash', $usuarioHash)
            ->whereIn('resultado', ['rechazado', 'auditoria'])
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    private function intentosFallidosRecientes($usuarioHash)
    {
        if (! $usuarioHash) {
            return [];
        }

        return VoucherPreconsulta::where('usuario_rut_hash', $usuarioHash)
            ->whereIn('resultado', ['rechazado', 'auditoria'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($preconsulta) {
                return [
                    'id' => $preconsulta->id,
                    'fecha' => $preconsulta->created_at ? $preconsulta->created_at->toDateTimeString() : null,
                    'resultado' => $preconsulta->resultado,
                    'motivo' => $preconsulta->motivo,
                    'prestador_tipo' => $preconsulta->prestador_tipo,
                    'fingerprint' => $preconsulta->request_fingerprint,
                ];
            })
            ->toArray();
    }

    private function agregarIntentoActual(array $intentos, VoucherPreconsulta $preconsulta, $resultado, $motivo)
    {
        if (! in_array($resultado, ['rechazado', 'auditoria'], true)) {
            return $intentos;
        }

        array_unshift($intentos, [
            'id' => $preconsulta->id,
            'fecha' => $preconsulta->created_at ? $preconsulta->created_at->toDateTimeString() : now()->toDateTimeString(),
            'resultado' => $resultado,
            'motivo' => $motivo,
            'prestador_tipo' => $preconsulta->prestador_tipo,
            'fingerprint' => $preconsulta->request_fingerprint,
        ]);

        return array_slice($intentos, 0, 10);
    }

    private function notificarAuditoria(
        VoucherPreconsulta $preconsulta,
        $motivo,
        array $contradicciones,
        $intentosFallidosCount,
        array $intentosFallidos,
        array $porques,
        array $contexto
    ) {
        $expediente = VoucherPreconsultaAuditoria::updateOrCreate(
            ['preconsulta_id' => $preconsulta->id],
            [
                'estado' => 'pendiente',
                'motivo' => $motivo,
                'contradicciones' => $contradicciones,
                'intentos_fallidos_count' => $intentosFallidosCount,
                'intentos_fallidos' => $intentosFallidos,
                'porques' => $porques,
                'contexto' => $contexto,
            ]
        );

        VoucherAuditoria::create([
            'voucher_id' => null,
            'accion' => 'preconsulta_auditoria',
            'usuario_tipo' => 'api_preconsulta',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Preconsulta '.$preconsulta->id.' enviada a auditoria. Motivo: '.$motivo,
            'ip' => request()->ip(),
        ]);

        AuditorNotificacion::create([
            'voucher_id' => null,
            'alerta_id' => null,
            'titulo' => 'Preconsulta de voucher requiere auditoria',
            'mensaje' => implode("\n", [
                'Expediente: '.$expediente->id,
                'Preconsulta: '.$preconsulta->id,
                'Motivo: '.$motivo,
                'Contradicciones: '.$this->resumenCodigos($contradicciones),
                'Intentos fallidos 7 dias: '.$intentosFallidosCount,
                'Por que: '.$this->resumenPorques($porques),
            ]),
            'leido' => false,
        ]);
    }

    private function contextoAuditoria(Request $request, array $data, $hashModo, $relacion)
    {
        return [
            'external_consulta_id' => $data['external_consulta_id'] ?? null,
            'prestador_tipo_solicitado' => $data['prestador_tipo'] ?? null,
            'servicio_codigo' => strtoupper(trim($data['servicio_codigo'] ?? '')) ?: null,
            'has_profesional_rut' => ! empty($data['profesional_rut']),
            'has_laboratorio_rut' => ! empty($data['laboratorio_rut']),
            'has_dependiente_rut' => ! empty($data['dependiente_rut']),
            'has_codigo_voucher_externo' => ! empty($data['codigo_voucher_externo']),
            'has_geolocation' => isset($data['geolocalizacion_lat'], $data['geolocalizacion_lng']),
            'geolocalizacion_lat' => $data['geolocalizacion_lat'] ?? null,
            'geolocalizacion_lng' => $data['geolocalizacion_lng'] ?? null,
            'hash_validacion_modo' => $hashModo,
            'relacion_autorizada_id' => $relacion ? $relacion->id : null,
            'ip' => $request->ip(),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ];
    }

    private function agregarContradiccion(array &$contradicciones, $codigo, $detalle, $impacto = null, array $contexto = [])
    {
        $item = [
            'codigo' => $codigo,
            'detalle' => $detalle,
            'impacto' => $impacto,
        ];

        if (! empty($contexto)) {
            $item['contexto'] = $contexto;
        }

        $contradicciones[] = $item;
    }

    private function agregarPorque(array &$porques, $codigo, $explicacion)
    {
        $porques[] = [
            'codigo' => $codigo,
            'explicacion' => $explicacion,
        ];
    }

    private function resumenCodigos(array $items)
    {
        if (empty($items)) {
            return 'sin contradicciones registradas';
        }

        return implode(', ', array_map(function ($item) {
            return $item['codigo'] ?? 'sin_codigo';
        }, array_slice($items, 0, 6)));
    }

    private function resumenPorques(array $items)
    {
        if (empty($items)) {
            return 'sin explicacion registrada';
        }

        return implode(' | ', array_map(function ($item) {
            return $item['explicacion'] ?? ($item['codigo'] ?? 'sin detalle');
        }, array_slice($items, 0, 3)));
    }

    private function prestadorResumen($profesional, $laboratorio)
    {
        if ($profesional) {
            return [
                'tipo' => 'profesional',
                'id' => $profesional->id,
                'nombre' => $profesional->nombre,
                'especialidad' => $profesional->especialidad,
                'nivel_bono' => $profesional->nivel_bono,
            ];
        }

        if ($laboratorio) {
            return [
                'tipo' => 'laboratorio',
                'id' => $laboratorio->id,
                'nombre' => $laboratorio->nombre,
                'especialidad' => $laboratorio->especialidad,
                'nivel_bono' => $laboratorio->nivel_bono,
            ];
        }

        return null;
    }

    private function prestadorTipo($data, $profesional, $laboratorio, $servicio)
    {
        if ($profesional) {
            return 'profesional';
        }
        if ($laboratorio) {
            return 'laboratorio';
        }
        if ($servicio) {
            return 'servicio';
        }

        return $data['prestador_tipo'] ?? null;
    }

    private function sanitizedPayload(array $data)
    {
        return [
            'external_consulta_id' => $data['external_consulta_id'] ?? null,
            'has_usuario_rut' => ! empty($data['usuario_rut']),
            'has_dependiente_rut' => ! empty($data['dependiente_rut']),
            'has_profesional_rut' => ! empty($data['profesional_rut']),
            'has_laboratorio_rut' => ! empty($data['laboratorio_rut']),
            'servicio_codigo' => $data['servicio_codigo'] ?? null,
            'has_geolocation' => isset($data['geolocalizacion_lat'], $data['geolocalizacion_lng']),
            'has_codigo_voucher_externo' => ! empty($data['codigo_voucher_externo']),
        ];
    }

    private function rutHash($rut)
    {
        return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key'));
    }

    private function rutSha256($rut)
    {
        return hash('sha256', $this->normalizarRut($rut));
    }

    private function normalizarRut($rut)
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }
}
