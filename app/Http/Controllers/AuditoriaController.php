<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\AuditorNotificacion;
use App\Models\VoucherAlerta;
use App\Models\VoucherAuditoria;
use App\Models\VoucherPreconsultaAuditoria;
use App\Models\VoucherCobro;
use Illuminate\Support\Facades\DB;
use App\Services\MedichileAgendaService;
use App\Models\VoucherRendicion;
use App\Models\VoucherLiquidacion;
use App\Models\VoucherProfesional;
use App\Models\PagoAutorizacion;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index()
    {
        $reviewSettings = $this->parametrosBonos();
        $auditorias = VoucherAuditoria::with(['voucher', 'usuario'])
            ->orderByRaw("COALESCE(usuario_tipo, 'sistema') ASC")
            ->orderByRaw('COALESCE(usuario_id, 0) ASC')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(200)
            ->get();

        $preconsultaAuditorias = VoucherPreconsultaAuditoria::with([
                'preconsulta.usuario',
                'preconsulta.dependiente',
                'preconsulta.profesional',
                'preconsulta.laboratorio',
                'preconsulta.servicio',
                'auditor',
            ])
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('id', 'desc')
            ->get();

        $notificacionesPendientes = AuditorNotificacion::where('leido', false)->count();
        $ultimasNotificaciones = AuditorNotificacion::latest()->take(5)->get();

        $cobrosAuditoria = VoucherCobro::with([
                'voucher.atencion',
                'voucher.agenda',
                'voucher.profesional',
                'auditor',
                'decisorPago',
                'rendicion.liquidaciones',
            ])
            ->where(function ($query) {
                $query->whereIn('estado', [
                    'pendiente_auditoria',
                    'observado_auditoria',
                    'rechazado_auditoria',
                    'pendiente_rendicion',
                ])->orWhere(function ($query) {
                    $query->whereIn('estado', ['objetado_pago', 'pagado'])
                        ->whereNotNull('auditor_id');
                });
            })
            ->orderByRaw("CASE WHEN estado = 'pendiente_auditoria' THEN 0 WHEN estado = 'observado_auditoria' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->take(50)
            ->get()
            ->each(function (VoucherCobro $cobro) {
                $controles = $this->evaluarControlesCobro($cobro);
                $cobro->setAttribute('controles_calculados', $controles);
                $cobro->setAttribute('controles_ok', collect($controles)->every(fn ($control) => $control['ok']));
            });

        return view('auditoria.index', compact(
            'auditorias',
            'preconsultaAuditorias',
            'notificacionesPendientes',
            'ultimasNotificaciones'
            ,'cobrosAuditoria', 'reviewSettings'
        ));
    }

    public function guardarParametrosBonos(Request $request)
    {
        $data = $request->validate([
            'check_qr_integrity' => 'nullable|boolean',
            'check_closed_attention' => 'nullable|boolean',
            'check_professional_relation' => 'nullable|boolean',
            'check_medsdi_schedule' => 'nullable|boolean',
            'check_amount' => 'nullable|boolean',
            'check_duplicates' => 'nullable|boolean',
            'amount_tolerance' => 'required|numeric|min:0|max:1000000',
            'max_charges_per_voucher' => 'required|integer|min:1|max:20',
        ]);

        foreach (array_keys($this->parametrosBonos()) as $key) {
            if (str_starts_with($key, 'check_')) {
                $data[$key] = $request->boolean($key);
            }
        }

        DB::table('contraloria_bono_settings')->updateOrInsert(
            ['id' => 1],
            ['data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]
        );

        VoucherAuditoria::create([
            'voucher_id' => null,
            'accion' => 'parametros_revision_bonos_actualizados',
            'usuario_tipo' => 'auditor',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Contraloría actualizó los parámetros automáticos de revisión de bonos.',
            'ip' => $request->ip(),
        ]);

        return redirect()->route('auditoria.index')->with('ok', 'Parámetros guardados. Los cobros fueron reevaluados con la nueva configuración.');
    }

    public function resolverCobro(Request $request, $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:aprobar,observar,rechazar',
            'observacion' => 'nullable|string|max:2000|required_if:decision,observar,rechazar',
        ]);

        return DB::transaction(function () use ($request, $data, $id) {
            $cobro = VoucherCobro::with(['voucher.atencion', 'voucher.agenda'])
                ->lockForUpdate()
                ->findOrFail($id);

            if (! in_array($cobro->estado, ['pendiente_auditoria', 'observado_auditoria'], true)) {
                return back()->with('error', 'Este expediente de cobro ya tiene una resolución definitiva.');
            }

            $controles = $this->evaluarControlesCobro($cobro);
            $todosAprobados = collect($controles)->every(fn ($control) => $control['ok']);

            if ($data['decision'] === 'aprobar' && ! $todosAprobados) {
                return back()->with('error', 'No se puede dar el visto bueno: existen controles rechazados. Observe o rechace el expediente.');
            }

            $estado = [
                'aprobar' => 'pendiente_rendicion',
                'observar' => 'observado_auditoria',
                'rechazar' => 'rechazado_auditoria',
            ][$data['decision']];
            $auditadoAt = now();
            $hash = hash('sha256', json_encode([
                'cobro_id' => $cobro->id,
                'voucher_id' => $cobro->voucher_id,
                'auditor_id' => auth()->id(),
                'decision' => $data['decision'],
                'controles' => $controles,
                'auditado_at' => $auditadoAt->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $cobro->update([
                'estado' => $estado,
                'auditor_id' => auth()->id(),
                'auditado_at' => $auditadoAt,
                'observacion_auditor' => $data['observacion'] ?: 'Controles automáticos conformes. Visto bueno otorgado.',
                'resultado_controles' => $controles,
                'hash_visto_bueno' => $hash,
                'pago_estado' => $data['decision'] === 'aprobar' ? 'pendiente_autorizacion' : 'bloqueado',
            ]);

            if ($data['decision'] !== 'aprobar') {
                VoucherAlerta::create([
                    'voucher_id' => $cobro->voucher_id,
                    'tipo_alerta' => 'auditoria_cobro_'.$data['decision'],
                    'nivel' => $data['decision'] === 'rechazar' ? 'rojo' : 'amarillo',
                    'descripcion' => $data['observacion'],
                    'resuelta' => false,
                ]);
            }

            VoucherAuditoria::create([
                'voucher_id' => $cobro->voucher_id,
                'accion' => 'cobro_auditoria_'.$data['decision'],
                'usuario_tipo' => 'auditor',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Auditoría de cobro '.$data['decision'].'. Controles conformes: '.($todosAprobados ? 'sí' : 'no').'. Huella: '.$hash,
                'ip' => $request->ip(),
            ]);

            $mensaje = $data['decision'] === 'aprobar'
                ? 'Visto bueno otorgado. El cobro quedó habilitado para rendición administrativa.'
                : 'Expediente marcado como '.$data['decision'].' y retirado del proceso automático.';

            return back()->with('ok', $mensaje);
        });
    }

    public function resolverPago(Request $request, $id)
    {
        $data = $request->validate([
            'decision_pago' => 'required|in:autorizar,objetar',
            'motivo_objecion' => 'nullable|string|max:2000|required_if:decision_pago,objetar',
        ]);

        return DB::transaction(function () use ($request, $data, $id) {
            $cobro = VoucherCobro::with(['voucher.atencion', 'voucher.agenda'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($cobro->estado !== 'pendiente_rendicion'
                || $cobro->pago_estado !== 'pendiente_autorizacion'
                || ! $cobro->hash_visto_bueno) {
                return back()->with('error', 'El pago no está pendiente de autorización o perdió su visto bueno.');
            }

            $controles = $this->evaluarControlesCobro($cobro);
            if (! collect($controles)->every(fn ($control) => $control['ok'])) {
                return back()->with('error', 'Pago bloqueado: uno o más controles dejaron de estar conformes.');
            }

            if ($data['decision_pago'] === 'objetar') {
                $cobro->update([
                    'estado' => 'objetado_pago',
                    'pago_estado' => 'objetado',
                    'pago_decidido_por' => auth()->id(),
                    'pago_decidido_at' => now(),
                    'pago_objecion' => $data['motivo_objecion'],
                ]);

                VoucherAuditoria::create([
                    'voucher_id' => $cobro->voucher_id,
                    'accion' => 'pago_objetado_contraloria',
                    'usuario_tipo' => 'auditor',
                    'usuario_id' => auth()->id(),
                    'descripcion' => 'Pago objetado por contraloría. Motivo: '.$data['motivo_objecion'],
                    'ip' => $request->ip(),
                ]);

                return back()->with('ok', 'Pago objetado. El expediente quedó bloqueado y no se realizó depósito.');
            }

            $profesional = VoucherProfesional::find($cobro->profesional_id);
            if (! $profesional
                || ! filled($profesional->banco)
                || ! filled($profesional->tipo_cuenta)
                || ! filled($profesional->numero_cuenta)) {
                return back()->with('error', 'Pago bloqueado: el profesional no tiene una cuenta bancaria completa registrada.');
            }

            $ahora = now();
            $comprobante = 'DEP-SDI-'.$ahora->format('YmdHis').'-'.str_pad((string) $cobro->id, 5, '0', STR_PAD_LEFT);
            $rendicion = VoucherRendicion::create([
                'veterinario_nombre' => $profesional->nombre,
                'sucursal' => $cobro->sucursal ?: 'Centro médico de prueba',
                'total_cobrado' => $cobro->monto_cobrado,
                'cantidad_vouchers' => 1,
                'estado' => 'pagada',
                'rendida_en' => $ahora,
                'pagada_en' => $ahora,
            ]);

            $cobro->update([
                'voucher_rendicion_id' => $rendicion->id,
                'estado' => 'pagado',
                'pago_estado' => 'depositado',
                'pago_decidido_por' => auth()->id(),
                'pago_decidido_at' => $ahora,
                'pago_objecion' => null,
                'deposito_comprobante' => $comprobante,
            ]);

            $liquidacion = VoucherLiquidacion::create([
                'voucher_rendicion_id' => $rendicion->id,
                'profesional_id' => $profesional->id,
                'profesional_nombre' => $profesional->nombre,
                'banco' => $profesional->banco,
                'tipo_cuenta' => $profesional->tipo_cuenta,
                'numero_cuenta' => $profesional->numero_cuenta,
                'monto_profesional' => $cobro->monto_cobrado,
                'comision_veterchile' => optional($cobro->voucher)->comision_veterchile ?: 0,
                'estado' => 'pagada',
                'medio_pago' => 'transferencia_simulada',
                'comprobante_transferencia' => $comprobante,
                'pagado_en' => $ahora,
            ]);

            PagoAutorizacion::create([
                'voucher_liquidacion_id' => $liquidacion->id,
                'voucher_rendicion_id' => $rendicion->id,
                'profesional_id' => $profesional->id,
                'admin_id' => auth()->id(),
                'token' => (string) Str::uuid(),
                'estado' => 'aprobada',
                'ip_solicitud' => $request->ip(),
                'ip_respuesta' => $request->ip(),
                'device_id' => 'SIMULADOR-DEPOSITO-SDI',
                'expira_at' => $ahora,
                'aprobada_at' => $ahora,
            ]);

            VoucherAuditoria::create([
                'voucher_id' => $cobro->voucher_id,
                'accion' => 'pago_autorizado_deposito_simulado',
                'usuario_tipo' => 'auditor',
                'usuario_id' => auth()->id(),
                'descripcion' => 'Pago autorizado y depósito simulado por $'.number_format($cobro->monto_cobrado, 0, ',', '.').' a '.$profesional->banco.', cuenta terminada en '.substr((string) $profesional->numero_cuenta, -4).'. Comprobante '.$comprobante.'.',
                'ip' => $request->ip(),
            ]);

            return back()->with('ok', 'Pago autorizado y depositado en la cuenta registrada. Comprobante: '.$comprobante);
        });
    }

    private function evaluarControlesCobro(VoucherCobro $cobro): array
    {
        $settings = $this->parametrosBonos();
        $voucher = $cobro->voucher;
        $atencion = optional($voucher)->atencion;
        $agenda = optional($voucher)->agenda;
        $firmaEsperada = $voucher
            ? hash_hmac('sha256', $voucher->id.$voucher->codigo, config('app.key'))
            : null;
        $montoCoincide = $voucher
            && abs((float) $cobro->monto_cobrado - (float) $voucher->saldo_veterinario) <= (float) $settings['amount_tolerance']
            && (float) $cobro->monto_cobrado > 0;
        $controlMedichile = ($voucher && $agenda)
            ? app(MedichileAgendaService::class)->verificarAtencionRealizada($voucher, $agenda)
            : ['ok' => false, 'detalle' => 'No existe una agenda asociada al bono.'];

        $controls = [
            ['setting' => 'check_qr_integrity', 'codigo' => 'QR_INTEGRO', 'nombre' => 'Integridad del QR', 'ok' => $voucher && filled($voucher->qr_firma) && hash_equals((string) $voucher->qr_firma, (string) $firmaEsperada), 'detalle' => 'Firma HMAC del bono sin alteraciones.'],
            ['setting' => 'check_closed_attention', 'codigo' => 'ATENCION_CERRADA', 'nombre' => 'Atención cerrada', 'ok' => $atencion && in_array($atencion->estado, ['validada_automaticamente', 'validada_por_asistente'], true) && filled($atencion->cerrada_at), 'detalle' => 'Cierre clínico y diagnóstico registrados.'],
            ['setting' => 'check_professional_relation', 'codigo' => 'RELACION_PROFESIONAL', 'nombre' => 'Paciente–profesional', 'ok' => $voucher && (int) $voucher->profesional_id === (int) $cobro->profesional_id && filled($voucher->beneficiario_nombre ?: $voucher->cliente_nombre), 'detalle' => 'El cobro pertenece al profesional asociado al paciente y bono.'],
            ['setting' => 'check_medsdi_schedule', 'codigo' => 'AGENDA_MEDICHILE', 'nombre' => 'Agenda Medichile', 'ok' => $controlMedichile['ok'], 'detalle' => $controlMedichile['detalle']],
            ['setting' => 'check_amount', 'codigo' => 'MONTO_CONSISTENTE', 'nombre' => 'Valor a cobrar', 'ok' => $montoCoincide, 'detalle' => 'Diferencia permitida: $'.number_format((float) $settings['amount_tolerance'], 0, ',', '.').'.'],
            ['setting' => 'check_duplicates', 'codigo' => 'SIN_DUPLICIDAD', 'nombre' => 'Cobros por bono', 'ok' => $voucher && $voucher->cobros()->count() <= (int) $settings['max_charges_per_voucher'], 'detalle' => 'Máximo configurado: '.$settings['max_charges_per_voucher'].' cobro(s) por bono.'],
        ];

        return collect($controls)->map(function ($control) use ($settings) {
            if (! $settings[$control['setting']]) {
                $control['ok'] = true;
                $control['detalle'] = 'Control desactivado por Contraloría.';
            }
            unset($control['setting']);

            return $control;
        })->all();
    }

    private function parametrosBonos(): array
    {
        $defaults = [
            'check_qr_integrity' => true,
            'check_closed_attention' => true,
            'check_professional_relation' => true,
            'check_medsdi_schedule' => true,
            'check_amount' => true,
            'check_duplicates' => true,
            'amount_tolerance' => 0,
            'max_charges_per_voucher' => 1,
        ];
        $stored = DB::table('contraloria_bono_settings')->where('id', 1)->value('data');

        return array_replace($defaults, $stored ? json_decode($stored, true) : []);
    }

    public function resolverPreconsulta(Request $request, $id)
    {
        $data = $request->validate([
            'resolucion' => 'required|string|min:10|max:3000',
        ]);

        $expediente = VoucherPreconsultaAuditoria::findOrFail($id);

        $expediente->update([
            'estado' => 'resuelto',
            'auditor_id' => auth()->id(),
            'resolucion' => $data['resolucion'],
            'resuelto_at' => now(),
        ]);

        VoucherAuditoria::create([
            'voucher_id' => null,
            'accion' => 'preconsulta_auditoria_resuelta',
            'usuario_tipo' => 'auditor',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Expediente de preconsulta '.$expediente->id.' resuelto por auditor.',
            'ip' => $request->ip(),
        ]);

        return back()->with('ok', 'Expediente de preconsulta resuelto.');
    }

    public function alertas()
    {
        $verdes = VoucherAlerta::where('nivel', 'verde')->count();
        $amarillas = VoucherAlerta::where('nivel', 'amarillo')->count();
        $rojas = VoucherAlerta::where('nivel', 'rojo')->count();

        $montoBloqueado = Voucher::whereIn(
            'id',
            VoucherAlerta::where('nivel', 'rojo')
                ->where('resuelta', 0)
                ->pluck('voucher_id')
        )->sum('saldo_veterinario');

        $alertas = VoucherAlerta::with('voucher')
            ->orderBy('id', 'desc')
            ->get();

        $alertasPorTipo = VoucherAlerta::selectRaw('tipo_alerta, count(*) as total')
            ->groupBy('tipo_alerta')
            ->get();

        $alertasPorNivel = VoucherAlerta::selectRaw('nivel, count(*) as total')
            ->groupBy('nivel')
            ->get();

        $topProfesionales = VoucherAlerta::join(
                'vouchers',
                'voucher_alertas.voucher_id',
                '=',
                'vouchers.id'
            )
            ->selectRaw('vouchers.profesional_id, COUNT(*) as total')
            ->groupBy('vouchers.profesional_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('admin.alertas', compact(
            'verdes',
            'amarillas',
            'rojas',
            'montoBloqueado',
            'alertas',
            'alertasPorTipo',
            'alertasPorNivel',
            'topProfesionales'
        ));
    }

    public function resolver($id)
    {
        $alerta = VoucherAlerta::findOrFail($id);

        $alerta->update([
            'resuelta' => true,
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $alerta->voucher_id,
            'accion' => 'alerta_resuelta',
            'usuario_tipo' => 'auditor',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Alerta '.$alerta->id.' marcada como resuelta por auditor.',
            'ip' => request()->ip(),
        ]);

        return back()->with('ok', 'Alerta resuelta.');
    }
}
