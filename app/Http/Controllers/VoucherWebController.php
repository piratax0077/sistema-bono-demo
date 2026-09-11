<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Models\Voucher;
use App\Models\VoucherProfesional;
use App\Models\VoucherAuditoria;
use App\Models\VoucherDeliveryRequest;
use App\Models\VoucherServicio;
use App\Models\VoucherBaseDependiente;
use App\Models\VoucherBaseUsuario;
use App\Models\Cliente;
use App\Models\ClienteSaldo;
use App\Models\Profesional;
use App\Models\AuditorNotificacion;
use App\Services\MedichileQrImageService;
use App\Services\MedichileAgendaService;
use App\Services\VoucherQrPayloadService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;



class VoucherWebController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (auth()->user()->rol == 'admin') {
            $vouchers = Voucher::orderBy('id', 'desc')->get();
        } elseif (auth()->user()->rol == 'vendedor') {
            $vouchers = Voucher::where('vendedor_id', auth()->user()->vendedor_id)
                ->orderBy('id', 'desc')
                ->get();
        } elseif (auth()->user()->rol == 'profesional') {
            $vouchers = Voucher::where('profesional_id', auth()->user()->profesional_id)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            abort(403);
        }

        return view('vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol != 'vendedor') {
            abort(403, 'Solo vendedores pueden emitir vouchers');
        }

        return view('vouchers.create');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol != 'vendedor') {
            abort(403, 'Solo vendedores pueden emitir vouchers');
        }

        $data = $request->validate([
            'cliente_nombre' => 'required|string|max:150',
            'cliente_rut' => 'required|string|max:30',
            'cliente_telefono' => 'required|string|max:50',
            'cliente_email' => 'nullable|email|max:150',
            'beneficiario_key' => 'required|string|max:80',
            'profesional_id' => 'required|exists:profesionales,id',
            'servicio_id' => 'required|exists:voucher_servicios,id',
        ]);

        $servicio = VoucherServicio::findOrFail($data['servicio_id']);
        $profesional = Profesional::activos()->findOrFail($data['profesional_id']);

        [$beneficiarioTipo, $beneficiarioId] = array_pad(explode(':', $data['beneficiario_key'], 2), 2, null);
        $baseUsuario = null;
        $dependiente = null;

        if ($beneficiarioTipo === 'titular') {
            $baseUsuario = VoucherBaseUsuario::where('estado', 'activo')->findOrFail($beneficiarioId);
        } elseif ($beneficiarioTipo === 'carga') {
            $dependiente = VoucherBaseDependiente::with('usuario')
                ->where('estado', 'activo')
                ->findOrFail($beneficiarioId);
            $baseUsuario = $dependiente->usuario;
        } else {
            return back()
                ->withInput()
                ->with('error', 'Seleccione si el voucher es para el titular o para una carga vigente.');
        }

        if (! $baseUsuario || $baseUsuario->estado !== 'activo') {
            return back()
                ->withInput()
                ->with('error', 'El titular no está vigente en la base externa.');
        }

        $titularRut = $this->decryptValue($baseUsuario->rut_encrypted) ?: $data['cliente_rut'];
        $titularRutNormalizado = $this->normalizarRut($titularRut);
        $titularOtros = is_array($baseUsuario->otros) ? $baseUsuario->otros : [];

        $beneficiarioRut = $dependiente
            ? $this->decryptValue($dependiente->rut_encrypted)
            : $titularRut;
        $beneficiarioDireccion = $dependiente
            ? $this->decryptValue($dependiente->direccion_encrypted)
            : $this->decryptValue($baseUsuario->direccion_encrypted);
        $beneficiarioFechaNacimiento = $dependiente
            ? $this->decryptValue($dependiente->fecha_nacimiento_encrypted)
            : $this->decryptValue($baseUsuario->fecha_nacimiento_encrypted);
        $beneficiarioEdad = $this->edadDesdeFecha($beneficiarioFechaNacimiento);
        $beneficiarioNombre = $dependiente ? $dependiente->nombre : $baseUsuario->nombre;
        $beneficiarioParentesco = $dependiente ? ($dependiente->parentesco ?: 'Carga') : 'Titular';
        $beneficiarioRutNormalizado = $this->normalizarRut($beneficiarioRut ?: $titularRut);

        $data['cliente_nombre'] = $baseUsuario->nombre ?: $data['cliente_nombre'];
        $data['cliente_rut'] = $titularRutNormalizado ?: $data['cliente_rut'];
        $data['cliente_telefono'] = $data['cliente_telefono'] ?: ($titularOtros['telefono'] ?? null);
        $data['cliente_email'] = $data['cliente_email'] ?? ($titularOtros['email'] ?? null);

        $cliente = Cliente::where('rut_hash', hash('sha256', $titularRutNormalizado))->first();

        $rutNormalizado = $titularRutNormalizado;
        $rutHash = hash('sha256', $rutNormalizado);
        $saldoDisponible = ClienteSaldo::where('cliente_rut_hash', $rutHash)
            ->where('estado', 'disponible')
            ->sum('monto');

        $valor = (float) $servicio->valor_base;
        $copago = (float) $servicio->copago_base;
        $comision = (float) $servicio->comision_veterchile;
        $saldoAplicado = min((float) $saldoDisponible, $copago);
        $copagoFinal = max(0, $copago - $saldoAplicado);
        $saldoVeterinario = max(0, $valor - $copago - $comision);
        $rutCifrado = Crypt::encryptString($rutNormalizado);
        $invalidacionesVendedor = Voucher::where('vendedor_id', auth()->user()->vendedor_id)
        ->where('estado', 'invalidado_cliente')
        ->count();

if ($invalidacionesVendedor >= 5) {
    return back()->with(
        'error',
        'Este vendedor tiene múltiples vouchers invalidados. Requiere revisión administrativa.'
    );
}
        $voucher = Voucher::create([
            'codigo' => strtoupper(Str::random(10)),
            'qr_token' => Str::random(80),

            'cliente_id' => optional($cliente)->id,
            'cliente_nombre' => $data['cliente_nombre'],
            'cliente_rut' => $rutCifrado,
            'cliente_rut_hash' => $rutHash,
            'cliente_telefono' => $data['cliente_telefono'],
            'cliente_email' => $data['cliente_email'] ?? null,

            'beneficiario_tipo' => $dependiente ? 'carga' : 'titular',
            'beneficiario_base_usuario_id' => $baseUsuario->id,
            'beneficiario_dependiente_id' => optional($dependiente)->id,
            'beneficiario_nombre' => $beneficiarioNombre,
            'beneficiario_rut' => $beneficiarioRutNormalizado ? Crypt::encryptString($beneficiarioRutNormalizado) : null,
            'beneficiario_rut_hash' => $beneficiarioRutNormalizado ? hash('sha256', $beneficiarioRutNormalizado) : null,
            'beneficiario_parentesco' => $beneficiarioParentesco,
            'beneficiario_direccion' => $this->encryptOptional($beneficiarioDireccion),
            'beneficiario_fecha_nacimiento' => $this->encryptOptional($beneficiarioFechaNacimiento),
            'beneficiario_edad' => $beneficiarioEdad,
            'servicio_id' => $servicio->id,
            'tipo_servicio' => $servicio->nombre,
            'profesional_id' => $profesional->id,
            'prestador_rut' => $profesional->rut_mostrable,
            'prestador_nombre' => $profesional->nombre_mostrable,
            'prestador_especialidad' => $profesional->especialidad_mostrable,
            'prestador_email' => $profesional->email_mostrable,
            'prestador_telefono' => $profesional->telefono_mostrable,
            'prestador_direccion' => $profesional->direccion_mostrable,

            'valor' => $valor,
            'valor_total' => $valor,
            'copago_usuario' => $copagoFinal,
            'saldo_cliente_aplicado' => $saldoAplicado,
            'comision_veterchile' => $comision,
            'saldo_veterinario' => $saldoVeterinario,

            'estado' => 'pendiente_cliente',
            'fecha_vencimiento' => now()->addDays(30),

            'vendedor_id' => auth()->user()->vendedor_id,
        ]);
        $montoPendienteAplicar = $saldoAplicado;

if ($montoPendienteAplicar > 0) {

    $saldos = ClienteSaldo::where('cliente_rut_hash', $rutHash)
        ->where('estado', 'disponible')
        ->orderBy('id', 'asc')
        ->get();

    foreach ($saldos as $saldo) {

        if ($montoPendienteAplicar <= 0) {
            break;
        }

        if ($saldo->monto <= $montoPendienteAplicar) {

            $saldo->update([
                'estado' => 'consumido',
                'voucher_consumido_id' => $voucher->id,
                'consumido_en' => now(),
            ]);

            $montoPendienteAplicar -= $saldo->monto;

        } else {

            $montoConsumido = $montoPendienteAplicar;
            $montoRestante = $saldo->monto - $montoConsumido;

            $saldo->update([
                'monto' => $montoConsumido,
                'estado' => 'consumido',
                'voucher_consumido_id' => $voucher->id,
                'consumido_en' => now(),
            ]);

            ClienteSaldo::create([
                'cliente_nombre' => $voucher->cliente_nombre,
                'cliente_rut_hash' => $rutHash,
                'monto' => $montoRestante,
                'origen' => 'saldo_restante',
                'estado' => 'disponible',
                'descripcion' => 'Saldo restante luego de aplicar crédito a voucher',
            ]);

            $montoPendienteAplicar = 0;
        }
    }

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'saldo_cliente_aplicado',
        'usuario_tipo' => 'vendedor',
        'usuario_id' => auth()->id(),
        'descripcion' => 'Saldo cliente aplicado al copago por $' . $saldoAplicado,
        'ip' => request()->ip(),
    ]);
}
        $voucher->qr_firma = hash_hmac(
            'sha256',
            $voucher->id . $voucher->codigo,
            config('app.key')
        );

        $voucher->qr_expira = now()->addDays(30);

        $otp = random_int(100000, 999999);

        $voucher->otp_hash = hash('sha256', $otp);
        $voucher->otp_expira = now()->addMinutes(10);

        $voucher->save();

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'voucher_emitido',
            'usuario_tipo' => 'vendedor',
            'usuario_id' => $voucher->vendedor_id,
            'descripcion' => 'Voucher emitido desde escritorio vendedor y enviado a aceptación del cliente',
            'ip' => request()->ip(),
        ]);

        $telefono = preg_replace('/[^0-9]/', '', $data['cliente_telefono']);

        if (substr($telefono, 0, 2) !== '56') {
            $telefono = '56' . ltrim($telefono, '0');
        }

       $mensaje =
            "SDI\n\n".
            "Tu código para activar el voucher {$voucher->codigo} es: {$otp}\n".
            "Vence en 10 minutos.\n\n".
            "Si NO reconoces este voucher, invalídalo aquí:\n".
            route('vouchers.invalidar', $voucher->qr_token);

        $whatsappUrl = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);

        return redirect()
            ->route('vouchers.show', $voucher->id)
            ->with('whatsapp_otp_url', $whatsappUrl)
            ->with('ok', 'Voucher creado correctamente.');
            $mensaje =
            "Voucher SDI\n\n".
            "Si NO reconoce este voucher puede invalidarlo aquí:\n\n".
            route('vouchers.invalidar',
                $voucher->qr_token);
                }

    public function show($id)
    {
        if (!auth()->check()) {
            abort(403);
        }

        $voucher = Voucher::findOrFail($id);

        if (auth()->user()->rol == 'vendedor'
            && $voucher->vendedor_id != auth()->user()->vendedor_id) {
            abort(403);
        }

        if (auth()->user()->rol == 'profesional'
            && $voucher->profesional_id != auth()->user()->profesional_id) {
            abort(403);
        }

        if (!in_array(auth()->user()->rol, ['admin', 'vendedor', 'profesional'])) {
            abort(403);
        }

        return view('vouchers.show', compact('voucher'));
    }

    public function marcarPagado($id)
    {
        $voucher = Voucher::findOrFail($id);

        $voucher->update([
            'estado' => 'activo',
        ]);

        if ($voucher->pagos()->count()) {
            $voucher->pagos()->latest()->first()->update([
                'estado_pago' => 'pagado',
                'metodo_pago' => 'manual',
            ]);
        }

        return redirect()
            ->route('vouchers.show', $voucher->id);
    }

    public function pagar($id)
    {
        $voucher = Voucher::findOrFail($id);

        if (auth()->user()->rol == 'vendedor'
            && $voucher->vendedor_id != auth()->user()->vendedor_id) {
            abort(403);
        }

        return view('vouchers.pagar', compact('voucher'));
    }

    public function procesarPago(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        if (auth()->user()->rol == 'vendedor'
            && $voucher->vendedor_id != auth()->user()->vendedor_id) {
            abort(403);
        }

        $pago = $voucher->pagos()->latest()->first();

        if (!$pago) {
            \App\Models\VoucherPago::create([
                'voucher_id' => $voucher->id,
                'monto_pagado_usuario' => $voucher->copago_usuario,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => 'pagado',
                'comprobante' => $request->comprobante,
            ]);
        } else {
            $pago->update([
                'monto_pagado_usuario' => $voucher->copago_usuario,
                'metodo_pago' => $request->metodo_pago,
                'estado_pago' => 'pagado',
                'comprobante' => $request->comprobante,
            ]);
        }

        $voucher->update([
            'estado' => 'activo',
        ]);
        if ($voucher->estado == 'invalidado_cliente') {
    return back()->with('error', 'Este voucher fue invalidado por el cliente.');
}
        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'pago_registrado',
            'usuario_tipo' => 'vendedor',
            'usuario_id' => $voucher->vendedor_id,
            'descripcion' => 'Pago registrado y voucher activado',
            'ip' => request()->ip(),
        ]);

        return redirect()
            ->route('vouchers.show', $voucher->id)
            ->with('ok', 'Pago registrado y voucher activado.');
    }

    // public function cobrar($id)
    // {
    //     $voucher = Voucher::findOrFail($id);
    //     if ($voucher->estado !== 'validado_atencion') {
    //     \App\Helpers\SecurityLogger::log(
    //     'intento_cobro_web_sin_validacion_clinica',
    //     'Voucher',
    //     $voucher->id,
    //     'rechazado',
    //     'Intento de cobro web sin atención validada',
    //     $voucher->cliente_id
    //      );

    //      return back()->with('error', 'Voucher no validado clínicamente');
    //     }

    //     if ($voucher->estado != 'activo') {
    //         return back()->with('error', 'El voucher no está activo.');
    //     }

    //     if (!$voucher->otp_validado_at) {
    //         VoucherAuditoria::create([
    //             'voucher_id' => $voucher->id,
    //             'accion' => 'cobro_rechazado_sin_otp',
    //             'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
    //             'usuario_id' => auth()->id(),
    //             'descripcion' => 'Intento de cobro sin validación OTP',
    //             'ip' => request()->ip(),
    //         ]);

    //         return back()->with('error', 'El cliente aún no ha validado el OTP.');
    //     }

    //     if ($voucher->qr_usado) {
    //         return back()->with('error', 'Este voucher ya fue usado.');
    //     }

    //     $voucher->update([
    //         'estado' => 'cobrado',
    //         'qr_usado' => true,
    //         'qr_usado_at' => now(),
    //         'usado_en' => now(),


    //     ]);
    //         if ($voucher->estado == 'invalidado_cliente') {
    //         return back()->with('error', 'Este voucher fue invalidado por el cliente.');
    //             }
    //             if ($voucher->estado !== 'validado_atencion') {
    //             \App\Helpers\SecurityLogger::log(
    //             'intento_cobro_web_sin_validacion_clinica',
    //             'Voucher',
    //             $voucher->id,
    //             'rechazado',
    //             'Intento de cobro web sin atención validada',
    //             $voucher->cliente_id
    //         );

    //         return back()->with('error', 'Voucher no validado clínicamente');
    //             }
    //             \App\Models\VoucherCobro::create([
    //                 'voucher_id' => $voucher->id,
    //                 'veterinario_nombre' => 'Veterinaria Demo',
    //                 'sucursal' => 'Sucursal Centro',
    //                 'monto_cobrado' => $voucher->saldo_veterinario,
    //                 'estado' => 'pendiente_rendicion',
    //                 'cobrado_en' => now(),
    //             ]);

    //             VoucherAuditoria::create([
    //                 'voucher_id' => $voucher->id,
    //                 'accion' => 'voucher_cobrado',
    //                 'usuario_tipo' => auth()->user()->rol,
    //                 'usuario_id' => auth()->id(),
    //                 'descripcion' => 'Voucher cobrado correctamente',
    //                 'ip' => request()->ip(),
    //             ]);

    //             return redirect()
    //                 ->route('vouchers.show', $voucher->id)
    //                 ->with('ok', 'Voucher cobrado correctamente.');
    // }
public function cobrar($id)
{
    $voucher = Voucher::findOrFail($id);

    // Igual que voucherProfesionalHabilitadoParaCobro(): un bono Med-SDI
    // externo se autoriza por prestador_nombre, no por profesional_id local.
    $esLocal = (int) $voucher->profesional_id === (int) auth()->user()->profesional_id;
    $esExterno = ! empty($voucher->prestador_nombre);

    if (auth()->user()->rol !== 'profesional' || (! $esLocal && ! $esExterno)) {
        abort(403);
    }

    if ($voucher->estado == 'invalidado_cliente') {
        return back()->with('error', 'Este voucher fue invalidado por el cliente.');
    }

    if ($voucher->estado !== 'validado_atencion') {
        \App\Helpers\SecurityLogger::log(
            'intento_cobro_web_sin_validacion_clinica',
            'Voucher',
            $voucher->id,
            'rechazado',
            'Intento de cobro web sin atención validada',
            $voucher->cliente_id
        );

        return back()->with('error', 'Voucher no validado clínicamente.');
    }

    if ($voucher->qr_usado) {
        return back()->with('error', 'Este voucher ya fue usado.');
    }

    $voucher->update([
        'estado' => 'cobrado',
        'qr_usado' => true,
        'qr_usado_at' => now(),
        'usado_en' => now(),
    ]);

    \App\Models\VoucherCobro::updateOrCreate(
        ['voucher_id' => $voucher->id],
        [
            // Bono externo sin profesional_id local: el cobro queda a nombre
            // del profesional autenticado que lo generó.
            'profesional_id' => $voucher->profesional_id ?? auth()->user()->profesional_id,
            'veterinario_nombre' => $voucher->prestador_nombre ?? 'Profesional',
            'sucursal' => 'Sucursal Centro',
            'monto_cobrado' => $voucher->saldo_veterinario,
            'estado' => 'pendiente_auditoria',
            'cobrado_en' => now(),
        ]
    );

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'voucher_cobrado_con_validacion_clinica',
        'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'sistema',
        'usuario_id' => auth()->id(),
        'descripcion' => 'QR de cobro enviado después de la atención y validación clínica automática',
        'ip' => request()->ip(),
    ]);

    return redirect()
        ->route('profesional.cobros')
        ->with('ok', 'QR enviado a cobro. Quedó pendiente del visto bueno de auditoría.');
}

public function cobrarSeleccionados(Request $request)
{
    $data = $request->validate([
        'voucher_ids' => ['required', 'array', 'min:1', 'max:100'],
        'voucher_ids.*' => ['required', 'integer', 'distinct'],
    ], [
        'voucher_ids.required' => 'Seleccione al menos una atención para enviar a cobros.',
        'voucher_ids.min' => 'Seleccione al menos una atención para enviar a cobros.',
    ]);

    abort_unless(auth()->user()->rol === 'profesional', 403);

    $procesados = DB::transaction(function () use ($data) {
        $vouchers = Voucher::whereIn('id', $data['voucher_ids'])->lockForUpdate()->get();
        $procesados = 0;

        foreach ($vouchers as $voucher) {
            $esLocal = (int) $voucher->profesional_id === (int) auth()->user()->profesional_id;
            $esExterno = ! empty($voucher->prestador_nombre);
            if ((! $esLocal && ! $esExterno)
                || $voucher->estado !== 'validado_atencion'
                || $voucher->qr_usado
                || $voucher->cobros()->exists()) {
                continue;
            }

            $voucher->update(['estado' => 'cobrado', 'qr_usado' => true, 'qr_usado_at' => now(), 'usado_en' => now()]);
            \App\Models\VoucherCobro::create([
                'voucher_id' => $voucher->id,
                'profesional_id' => $voucher->profesional_id ?? auth()->user()->profesional_id,
                'veterinario_nombre' => $voucher->prestador_nombre ?? 'Profesional',
                'sucursal' => 'Sucursal Centro',
                'monto_cobrado' => $voucher->saldo_veterinario,
                'estado' => 'pendiente_auditoria',
                'cobrado_en' => now(),
            ]);
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'voucher_enviado_cobro_masivo',
                'usuario_tipo' => auth()->user()->rol,
                'usuario_id' => auth()->id(),
                'descripcion' => 'Bono enviado a cobro mediante selección masiva del profesional.',
                'ip' => request()->ip(),
            ]);
            $procesados++;
        }

        return $procesados;
    });

    return $procesados > 0
        ? redirect()->route('profesional.cobros')->with('ok', $procesados.' '.($procesados === 1 ? 'bono fue enviado' : 'bonos fueron enviados').' a cobros y quedaron pendientes de auditoría.')
        : back()->with('error', 'Ninguno de los bonos seleccionados estaba disponible para cobro.');
}

public function guardarProgramacionCobro(Request $request)
{
    abort_unless(auth()->user()->rol === 'profesional', 403);

    $data = $request->validate([
        'accion' => ['required', 'in:activar,desactivar'],
        'frecuencia' => ['required', 'in:diario,semanal,quincenal,mensual'],
    ]);

    $activo = $data['accion'] === 'activar';

    $programacion = \App\Models\VoucherCobroProgramacion::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'frecuencia' => $data['frecuencia'],
            'activo' => $activo,
            'proxima_ejecucion_at' => $activo
                ? \App\Models\VoucherCobroProgramacion::siguienteEjecucion($data['frecuencia'])
                : null,
        ]
    );

    return redirect()->route('profesional.cobros')->with(
        'ok',
        $programacion->activo
            ? 'Programación guardada correctamente. El cobro automático quedó activado. Próxima ejecución: '.$programacion->proxima_ejecucion_at->format('d-m-Y H:i').'.'
            : 'Cobro automático desactivado.'
    );
}

public function generarQrCobro($id)
{
    $voucher = $this->voucherProfesionalHabilitadoParaCobro($id);

    $urlFirmada = URL::temporarySignedRoute(
        'profesional.cobros.qr',
        now()->addHours(24),
        ['id' => $voucher->id]
    );

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'qr_cobro_generado',
        'usuario_tipo' => auth()->user()->rol,
        'usuario_id' => auth()->id(),
        'descripcion' => 'QR de cobro generado con enlace firmado y vigencia de 24 horas',
        'ip' => request()->ip(),
    ]);

    return redirect()->to($urlFirmada);
}

public function qrCobro($id)
{
    $voucher = $this->voucherProfesionalHabilitadoParaCobro($id);
    $voucher->load(['agenda', 'atencion']);

    $fechaAtencion = optional($voucher->atencion)->cerrada_at
        ?: optional($voucher->atencion)->fin_atencion
        ?: optional($voucher->agenda)->fecha_hora_confirmada;

    $lugarAtencion = optional($voucher->atencion)->direccion
        ?: $voucher->prestador_direccion
        ?: 'Centro médico de prueba';

    $datosCobro = [
        'codigo_bono' => $voucher->codigo,
        'paciente' => $voucher->beneficiario_nombre ?: $voucher->cliente_nombre,
        'profesional' => $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre,
        'relacion' => 'Paciente atendido por profesional asociado al bono',
        'lugar_atencion' => $lugarAtencion,
        'fecha_atencion' => $fechaAtencion ? Carbon::parse($fechaAtencion)->format('d-m-Y H:i') : 'Sin fecha registrada',
        'tipo_atencion' => $voucher->tipo_servicio ?: $voucher->prestador_especialidad ?: 'Consulta médica',
        'valor_a_cobrar' => (int) $voucher->saldo_veterinario,
        'estado' => 'Habilitado para cobro',
    ];

    $urlQr = request()->fullUrl();
    $qrSvg = (string) QrCode::format('svg')
        ->size(330)
        ->margin(2)
        ->errorCorrection('H')
        ->generate($urlQr);
    $qrDataUri = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
    $mensajeWhatsapp = 'QR de cobro Medichile · Bono '.$voucher->codigo
        .' · Paciente: '.$datosCobro['paciente']
        .' · Profesional: '.$datosCobro['profesional']
        .' · Valor a cobrar: $'.number_format($datosCobro['valor_a_cobrar'], 0, ',', '.')
        .' · Enlace seguro: '.$urlQr;

    return view('profesional.qr_cobro', compact(
        'voucher',
        'datosCobro',
        'qrDataUri',
        'mensajeWhatsapp'
    ));
}

/**
 * Mismos datos que qrCobro(), en JSON, para abrirlos en un modal desde
 * "Atenciones cerradas" sin redirigir a la página completa.
 */
public function cobroQrDatos($id)
{
    $voucher = $this->voucherProfesionalHabilitadoParaCobro($id);
    $voucher->load(['agenda', 'atencion']);

    $fechaAtencion = optional($voucher->atencion)->cerrada_at
        ?: optional($voucher->atencion)->fin_atencion
        ?: optional($voucher->agenda)->fecha_hora_confirmada;

    $lugarAtencion = optional($voucher->atencion)->direccion
        ?: $voucher->prestador_direccion
        ?: 'Centro médico de prueba';

    $datosCobro = [
        'codigo_bono' => $voucher->codigo,
        'paciente' => $voucher->beneficiario_nombre ?: $voucher->cliente_nombre,
        'profesional' => $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre,
        'relacion' => 'Paciente atendido por profesional asociado al bono',
        'lugar_atencion' => $lugarAtencion,
        'fecha_atencion' => $fechaAtencion ? Carbon::parse($fechaAtencion)->format('d-m-Y H:i') : 'Sin fecha registrada',
        'tipo_atencion' => $voucher->tipo_servicio ?: $voucher->prestador_especialidad ?: 'Consulta médica',
        'valor_a_cobrar' => (int) $voucher->saldo_veterinario,
    ];

    $urlFirmada = URL::temporarySignedRoute(
        'profesional.cobros.qr',
        now()->addHours(24),
        ['id' => $voucher->id]
    );

    $qrSvg = (string) QrCode::format('svg')
        ->size(330)
        ->margin(2)
        ->errorCorrection('H')
        ->generate($urlFirmada);
    $qrDataUri = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
    $mensajeWhatsapp = 'QR de cobro Medichile · Bono '.$voucher->codigo
        .' · Paciente: '.$datosCobro['paciente']
        .' · Profesional: '.$datosCobro['profesional']
        .' · Valor a cobrar: $'.number_format($datosCobro['valor_a_cobrar'], 0, ',', '.')
        .' · Enlace seguro: '.$urlFirmada;

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'qr_cobro_generado',
        'usuario_tipo' => auth()->user()->rol,
        'usuario_id' => auth()->id(),
        'descripcion' => 'QR de cobro generado con enlace firmado y vigencia de 24 horas',
        'ip' => request()->ip(),
    ]);

    return response()->json(array_merge($datosCobro, [
        'qr_data_uri' => $qrDataUri,
        'mensaje_whatsapp' => $mensajeWhatsapp,
        'signed_url' => $urlFirmada,
        'cobrar_url' => route('vouchers.cobrar', $voucher->id),
    ]));
}

private function voucherProfesionalHabilitadoParaCobro($id): Voucher
{
    $voucher = Voucher::with(['agenda', 'atencion', 'profesional'])->findOrFail($id);

    // Los bonos agendados vía Med-SDI externo no tienen profesional_id local
    // (el prestador viene de la API real), se identifican por prestador_nombre.
    $esLocal = (int) $voucher->profesional_id === (int) auth()->user()->profesional_id;
    $esExterno = ! empty($voucher->prestador_nombre);

    if (auth()->user()->rol !== 'profesional' || (! $esLocal && ! $esExterno)) {
        abort(403);
    }

    if ($voucher->estado !== 'validado_atencion' || $voucher->qr_usado || $voucher->cobros()->exists()) {
        abort(409, 'El bono no está disponible para generar un QR de cobro.');
    }

    return $voucher;
}
    public function qr($token, VoucherQrPayloadService $qrPayloadService, MedichileQrImageService $qrImageService)
    {
        $voucher = Voucher::where('qr_token', $token)
            ->with(['pagos', 'atencion', 'agenda', 'cobros.rendicion.liquidaciones'])
            ->firstOrFail();

        // El QR representa el bono ya pagado; sin esto, quien tenga el enlace
        // podría verlo antes de completar el copago.
        abort_unless($voucher->estado === 'activo', 404);

        $qrPayload = $qrPayloadService->build($voucher);
        $qrText = $qrPayloadService->compactText($voucher);
        $qrUrl = $qrPayload['qr_url'];
        $qrImage = $qrImageService->ensureForVoucher($voucher, $qrUrl);
        $centroWhatsapp = VoucherDeliveryRequest::where('voucher_id', $voucher->id)
            ->where('canal', 'medical_center_whatsapp')->latest('id')->value('destino');

        return view('vouchers.qr', compact('voucher', 'qrPayload', 'qrText', 'qrUrl', 'qrImage', 'centroWhatsapp'));
    }

    /**
     * Datos del modal "Compartir" en JSON, para abrirlo sin recargar la
     * página (ej. desde el historial de bonos del dashboard del paciente).
     */
    public function compartirDatos($token, MedichileQrImageService $qrImageService)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();
        abort_unless($voucher->estado === 'activo', 404);

        if (auth()->check() && auth()->user()->rol === 'cliente') {
            abort_unless((int) $voucher->cliente_id === (int) auth()->id(), 403);
        }

        $qrUrl = route('vouchers.qr', $voucher->qr_token);
        $qrImage = $qrImageService->ensureForVoucher($voucher, $qrUrl);
        $centroWhatsapp = VoucherDeliveryRequest::where('voucher_id', $voucher->id)
            ->where('canal', 'medical_center_whatsapp')->latest('id')->value('destino');

        $normalizarTelefono = function ($telefono) {
            $digits = preg_replace('/\D+/', '', (string) $telefono);
            if ($digits !== '' && substr($digits, 0, 2) !== '56') {
                $digits = '56'.ltrim($digits, '0');
            }

            return $digits;
        };

        $recipients = [];
        $addRecipient = function ($id, $label, $role, $phone = null, $email = null) use (&$recipients, $normalizarTelefono) {
            $phone = $normalizarTelefono($phone);
            $email = trim((string) $email);
            if ($phone === '' && $email === '') {
                return;
            }

            $channels = [];
            if ($phone !== '') {
                $channels[] = 'whatsapp';
            }
            if ($email !== '') {
                $channels[] = 'email';
            }

            $recipients[] = [
                'id' => $id,
                'label' => $label ?: 'Sin nombre',
                'role' => $role,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'channels' => $channels,
            ];
        };

        $addRecipient('titular', $voucher->cliente_nombre ?: 'Titular', 'Titular / paciente', $voucher->cliente_telefono, $voucher->cliente_email);
        $addRecipient('profesional', $voucher->prestador_nombre ?: 'Profesional', 'Profesional tratante', $voucher->prestador_telefono, $voucher->prestador_email);
        $addRecipient('centro-medico', 'Centro médico / recepción', 'Recepción del centro', $centroWhatsapp, $voucher->centro_email ?: 'recepcion@centromedico.cl');

        $qrImageUrl = $qrImage['url'] ?? null;
        $qrImagePath = $qrImage['path'] ?? null;
        $qrImageVersion = $qrImagePath && file_exists($qrImagePath) ? filemtime($qrImagePath) : time();

        return response()->json([
            'codigo' => $voucher->codigo,
            'recipients' => $recipients,
            'subject' => 'QR Medichile '.$voucher->codigo,
            'message' => implode("\n", [
                'Medichile · Bono digital seguro',
                '',
                'QR del bono: '.$voucher->codigo,
                '',
                'Se adjunta imagen QR. No es necesario imprimir.',
            ]),
            'qr_image_url' => $qrImageUrl.'?v='.$qrImageVersion,
            'qr_image_download_name' => 'qr-medichile-'.$voucher->codigo.'.png',
            'whatsapp_demo_url' => route('vouchers.qr.whatsappDemo', $voucher->qr_token),
            'qr_page_url' => $qrUrl,
        ]);
    }

    public function whatsappDemo(Request $request, $token, VoucherQrPayloadService $qrPayloadService, MedichileQrImageService $qrImageService)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();
        $user = $request->user();

        if ($user->rol === 'cliente' && (int) $voucher->cliente_id !== (int) $user->id) {
            abort(403);
        }

        abort_unless($voucher->estado === 'activo', 404);

        $qrPayload = $qrPayloadService->build($voucher);
        $qrImage = $qrImageService->ensureForVoucher($voucher, $qrPayload['qr_url']);
        $centroWhatsapp = VoucherDeliveryRequest::where('voucher_id', $voucher->id)
            ->where('canal', 'medical_center_whatsapp')->latest('id')->value('destino');
        $destino = in_array($request->query('destino'), ['titular', 'profesional', 'centro-medico'], true)
            ? $request->query('destino')
            : 'titular';
        $contactos = [
            'titular' => [
                'nombre' => data_get($qrPayload, 'titular.nombre') ?: $voucher->cliente_nombre,
                'detalle' => data_get($qrPayload, 'titular.telefono') ?: $voucher->cliente_telefono ?: 'Paciente',
            ],
            'profesional' => [
                'nombre' => $voucher->prestador_nombre ?: 'Profesional tratante',
                'detalle' => $voucher->prestador_telefono ?: $voucher->prestador_email ?: 'Profesional',
            ],
            'centro-medico' => [
                'nombre' => 'Centro médico / recepción',
                'detalle' => $centroWhatsapp ?: 'Falta teléfono WhatsApp del centro médico',
            ],
        ];
        $contacto = $contactos[$destino];

        return view('vouchers.whatsapp_demo', compact('voucher', 'qrPayload', 'qrImage', 'destino', 'contactos', 'contacto'));
    }

    public function enviarWhatsappDemo(Request $request, $token, VoucherQrPayloadService $qrPayloadService)
    {
        $data = $request->validate(['destino' => 'required|in:titular,profesional,centro-medico']);
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();
        $user = $request->user();

        if ($user->rol === 'cliente' && (int) $voucher->cliente_id !== (int) $user->id) {
            abort(403);
        }

        $payload = $qrPayloadService->build($voucher);
        $destinos = [
            'titular' => ['nombre' => data_get($payload, 'titular.nombre') ?: $voucher->cliente_nombre, 'telefono' => data_get($payload, 'titular.telefono') ?: $voucher->cliente_telefono],
            'profesional' => ['nombre' => $voucher->prestador_nombre ?: 'Profesional tratante', 'telefono' => $voucher->prestador_telefono],
            'centro-medico' => ['nombre' => 'Centro médico / recepción', 'telefono' => VoucherDeliveryRequest::where('voucher_id', $voucher->id)->where('canal', 'medical_center_whatsapp')->latest('id')->value('destino')],
        ];
        $destinatario = $destinos[$data['destino']];
        $telefono = preg_replace('/\D+/', '', (string) $destinatario['telefono']);

        if ($telefono === '') {
            return back()->with('whatsapp_demo_error', 'Falta teléfono WhatsApp del '.$destinatario['nombre'].'.');
        }

        VoucherDeliveryRequest::create([
            'voucher_id' => $voucher->id,
            'cliente_user_id' => $voucher->cliente_id,
            'canal' => 'whatsapp_demo',
            'destino_tipo' => $destinatario['nombre'],
            'destino' => '+'.$telefono,
            'estado' => 'sent_demo',
            'mensaje' => 'Simulación de envío WhatsApp del bono '.$voucher->codigo.' con imagen QR.',
            'action_url' => route('vouchers.qr', $voucher->qr_token),
            'enviado_en' => now(),
            'metadata' => ['simulacion' => true, 'destino' => $data['destino'], 'no_envio_real' => true],
        ]);
        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'whatsapp_demo_enviado',
            'usuario_tipo' => $user->rol,
            'usuario_id' => $user->id,
            'descripcion' => 'Entrega WhatsApp simulada a '.$destinatario['nombre'].' (+'.$telefono.'). No se contactó un servicio externo.',
            'ip' => $request->ip(),
        ]);

        return redirect()->route('vouchers.qr.whatsappDemo', ['token' => $voucher->qr_token, 'destino' => $data['destino']])
            ->with('whatsapp_demo_ok', 'Envío simulado registrado para '.$destinatario['nombre'].' al +'.$telefono.'.');
    }

    public function simularLectorQr($token, VoucherQrPayloadService $qrPayloadService)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();
        $user = auth()->user();

        if ($user->rol === 'cliente' && (int) $voucher->cliente_id !== (int) $user->id) {
            abort(403);
        }

        abort_unless($voucher->estado === 'activo', 404);

        $qrPayload = $qrPayloadService->build($voucher);
        $firmaCalculada = hash_hmac(
            'sha256',
            json_encode(collect($qrPayload)->except('integridad')->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (string) config('app.key')
        );
        $firmaValida = hash_equals((string) ($qrPayload['integridad']['firma'] ?? ''), $firmaCalculada);
        $entregas = $voucher->hasMany(\App\Models\VoucherDeliveryRequest::class)->orderBy('id')->get();
        $recepcion = $entregas->firstWhere('canal', 'assistant_totem_reception');
        $cobro = $voucher->cobros->first();
        $liquidacion = $cobro?->rendicion?->liquidaciones?->first();
        $fechaIso = static fn ($fecha) => filled($fecha) ? Carbon::parse($fecha)->toIso8601String() : null;
        $etapas = [
            ['etapa' => 'Compra y copago', 'estado' => $voucher->pagos->where('estado_pago', 'pagado')->isNotEmpty() ? 'completada' : 'pendiente', 'fecha' => $fechaIso(optional($voucher->pagos->sortByDesc('id')->first())->created_at), 'detalle' => 'Bono emitido con copago de $'.number_format($voucher->copago_usuario, 0, ',', '.').'.'],
            ['etapa' => 'Envío del QR', 'estado' => $entregas->isNotEmpty() ? 'completada' : 'pendiente', 'fecha' => $fechaIso(optional($entregas->last())->enviado_en), 'detalle' => $entregas->isNotEmpty() ? $entregas->count().' entrega(s) registrada(s).' : 'Aún no se registra envío.'],
            ['etapa' => 'Recepción del paciente', 'estado' => optional($recepcion)->estado === 'received' || $voucher->agenda_id || $voucher->atencion ? 'completada' : 'pendiente', 'fecha' => data_get(optional($recepcion)->metadata, 'recibido_en'), 'detalle' => optional($recepcion)->estado === 'received' || $voucher->agenda_id || $voucher->atencion ? 'Paciente reconocido por su bono asociado.' : 'Pendiente de llegada al centro médico.'],
            ['etapa' => 'Atención profesional', 'estado' => $voucher->atencion ? (in_array($voucher->atencion->estado, ['cerrada_por_profesional', 'validada_por_asistente', 'validada_automaticamente'], true) ? 'completada' : 'en_proceso') : 'pendiente', 'fecha' => $fechaIso(optional($voucher->atencion)->cerrada_at), 'detalle' => $voucher->atencion ? 'Estado clínico: '.$voucher->atencion->estado.'.' : 'Aún no iniciada.'],
            ['etapa' => 'Validación para cobro', 'estado' => $voucher->validado_at ? 'completada' : 'pendiente', 'fecha' => $fechaIso($voucher->validado_at), 'detalle' => $voucher->validado_at ? 'Bono habilitado para cobro.' : 'Pendiente de validación.'],
        ];
        if (in_array($user->rol, ['admin', 'auditor', 'asistente', 'profesional'], true)) {
            $etapas[] = ['etapa' => 'Cobro administrativo', 'estado' => $cobro ? 'completada' : 'pendiente', 'fecha' => $fechaIso(optional($cobro)->cobrado_en), 'detalle' => $cobro ? 'Cobro registrado con estado '.$cobro->estado.'.' : 'Aún no enviado a cobro.'];
            $etapas[] = ['etapa' => 'Pago al profesional', 'estado' => $liquidacion && $liquidacion->estado === 'pagada' ? 'completada' : 'pendiente', 'fecha' => $fechaIso(optional($liquidacion)->pagado_en), 'detalle' => $liquidacion && $liquidacion->estado === 'pagada' ? 'Liquidación depositada y disponible para contraloría.' : 'Pendiente de liquidación y depósito.'];
        }
        $lectura = array_merge($qrPayload, ['proceso' => $etapas]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'lector_qr_simulado',
            'usuario_tipo' => $user->rol,
            'usuario_id' => $user->id,
            'descripcion' => 'Lectura simulada del contenido público del QR. Firma '.($firmaValida ? 'válida' : 'no válida').'.',
            'ip' => request()->ip(),
        ]);

        return view('vouchers.lector_qr_demo', compact('voucher', 'qrPayload', 'firmaValida', 'etapas', 'lectura'));
    }

    public function validarPantalla($token, VoucherQrPayloadService $qrPayloadService)
    {
        $voucher = Voucher::where('qr_token', $token)->first();
        $qrPayload = null;
        $motivos = [];
        $ok = false;
        $nivel = 'danger';
        $mensaje = 'Voucher no encontrado.';

        if ($voucher) {
            $qrPayload = $qrPayloadService->build($voucher);
            $firmaCalculada = hash_hmac(
                'sha256',
                $voucher->id . $voucher->codigo,
                config('app.key')
            );

            $ok = true;
            $nivel = 'success';
            $mensaje = 'Voucher válido para revisión.';

            if ($voucher->estado === 'invalidado_cliente') {
                $ok = false;
                $nivel = 'danger';
                $motivos[] = 'El cliente invalidó este voucher.';
            }

            if (! $voucher->qr_firma || ! hash_equals((string) $voucher->qr_firma, $firmaCalculada)) {
                $ok = false;
                $nivel = 'danger';
                $motivos[] = 'La firma de seguridad del voucher no coincide.';
            }

            if ($voucher->qr_expira && now()->gt($voucher->qr_expira)) {
                $ok = false;
                $nivel = 'danger';
                $motivos[] = 'El token QR está vencido.';
            }

            if ($voucher->qr_usado) {
                $ok = false;
                $nivel = 'danger';
                $motivos[] = 'El voucher ya fue usado.';
            }

            if ($ok && ! in_array($voucher->estado, [
                'activo',
                'pendiente_cliente',
                'pendiente_pago',
                'asignado',
                'en_atencion',
                'atencion_cerrada',
                'validado_atencion',
            ], true)) {
                $nivel = 'warning';
                $motivos[] = 'El voucher existe y la firma es correcta, pero su estado requiere revisión: '.$voucher->estado.'.';
            }

            if (! $motivos) {
                $motivos[] = 'Token, firma HMAC, vencimiento y uso revisados correctamente.';
            }

            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => $ok ? 'voucher_validado_pantalla' : 'voucher_validacion_pantalla_rechazada',
                'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'sistema',
                'usuario_id' => auth()->id(),
                'descripcion' => implode(' ', $motivos),
                'ip' => request()->ip(),
            ]);

            $mensaje = $ok
                ? ($nivel === 'warning' ? 'Voucher requiere revisión.' : 'Voucher válido.')
                : 'Voucher no válido.';
        }

        return view('vouchers.validacion', compact(
            'voucher',
            'qrPayload',
            'ok',
            'nivel',
            'mensaje',
            'motivos'
        ));
    }

    public function usar($token)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();

        $profesionales = VoucherProfesional::where('activo', 1)
            ->orderBy('nombre')
            ->get();
if ($voucher->estado == 'invalidado_cliente') {
    return back()->with('error', 'Este voucher fue invalidado por el cliente.');
}
        return view('vouchers.usar', compact('voucher', 'profesionales'));
    }

    public function asignarProfesional(Request $request, $token)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();

        if ($voucher->profesional_id) {
            return back()->with('error', 'Este voucher ya fue asignado.');
        }

        $voucher->update([
            'profesional_id' => $request->profesional_id,
            'estado' => 'asignado',
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'profesional_asignado',
            'usuario_tipo' => 'cliente',
            'usuario_id' => null,
            'descripcion' => 'Voucher asignado digitalmente al profesional ID ' . $request->profesional_id,
            'ip' => request()->ip(),
        ]);

        return redirect()
            ->route('vouchers.qr', $voucher->qr_token)
            ->with('ok', 'Voucher asignado correctamente.');
    }

    public function atencionProfesional($id)
    {
        $voucher = Voucher::with(['agenda', 'atencion'])->findOrFail($id);

        if (auth()->user()->rol !== 'profesional'
            || (int) $voucher->profesional_id !== (int) auth()->user()->profesional_id) {
            abort(403);
        }

        if (! in_array($voucher->estado, ['en_atencion', 'atencion_cerrada'], true)) {
            return redirect('/escritorio-profesional')->with('error', 'Primero debe abrir la atención desde pacientes en espera.');
        }

        return view('profesional.atencion_demo', compact('voucher'));
    }

    public function aceptarAtencion($id, MedichileAgendaService $medichileAgenda)
    {
        $voucher = Voucher::with('agenda')->findOrFail($id);

        if (auth()->user()->rol != 'profesional'
            || $voucher->profesional_id != auth()->user()->profesional_id) {
            abort(403);
        }

        if (! in_array($voucher->estado, ['asignado', 'en_atencion'], true)) {
            return back()->with('error', 'El bono no está disponible para iniciar atención.');
        }

        if (! $voucher->agenda || $voucher->agenda->estado !== 'paciente_en_espera') {
            return back()->with('error', 'El paciente todavía no está confirmado en sala de espera.');
        }

        $sync = $medichileAgenda->marcarAtencionIniciada($voucher, $voucher->agenda);
        $inicio = now();
        $atencion = \App\Models\VoucherAtencion::updateOrCreate(
            ['voucher_id' => $voucher->id],
            [
                'agenda_id' => $voucher->agenda->id,
                'cliente_id' => $voucher->cliente_id,
                'profesional_id' => auth()->user()->profesional_id,
                'inicio_atencion' => optional($voucher->atencion)->inicio_atencion ?: $inicio,
                'estado' => 'abierta',
                'riesgo' => 'bajo',
                'ip_profesional' => request()->ip(),
                'user_agent_profesional' => request()->userAgent(),
            ]
        );

        $voucher->update([
            'estado' => 'en_atencion',
            'atencion_id' => $atencion->id,
            'profesional_atendio_id' => auth()->user()->profesional_id,
        ]);
        $voucher->agenda->update([
            'medichile_estado_id' => $sync['estado_id'],
            'medichile_sincronizado_at' => $sync['sincronizado_at'],
            'medichile_sync_error' => null,
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'atencion_iniciada_profesional_medichile',
            'usuario_tipo' => 'profesional',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Atención iniciada. Hora Medichile #'.$sync['hora_medica_id'].' actualizada a Realizando (ID 5).',
            'ip' => request()->ip(),
        ]);

        return redirect()->route('profesional.vouchers.atencion', $voucher->id)
            ->with('ok', 'Atención iniciada. Registre el diagnóstico para cerrarla.');
    }

    public function finalizarAtencion(Request $request, $id, MedichileAgendaService $medichileAgenda)
{
    $data = $request->validate([
        'diagnostico' => ['required', 'string', 'min:10', 'max:2000'],
        'observacion' => ['nullable', 'string', 'max:2000'],
    ], [
        'diagnostico.required' => 'Ingrese el diagnóstico antes de cerrar la atención.',
        'diagnostico.min' => 'El diagnóstico debe contener al menos 10 caracteres.',
    ]);

    $voucher = Voucher::with(['agenda', 'atencion'])->findOrFail($id);

    if (auth()->user()->rol != 'profesional'
        || $voucher->profesional_id != auth()->user()->profesional_id) {
        abort(403);
    }

    if ($voucher->estado !== 'en_atencion' || ! $voucher->agenda) {
        return back()->with('error', 'El voucher no está disponible para finalizar atención.');
    }

    $sync = $medichileAgenda->marcarAtencionCerrada($voucher, $voucher->agenda);
    $cerrada = now();

    $atencion = \App\Models\VoucherAtencion::updateOrCreate(
        ['voucher_id' => $voucher->id],
        [
            'agenda_id' => $voucher->agenda_id,
            'cliente_id' => $voucher->cliente_id,
            'mascota_id' => $voucher->mascota_id,
            'profesional_id' => auth()->user()->profesional_id,
            'inicio_atencion' => optional($voucher->atencion)->inicio_atencion ?: $cerrada,
            'fin_atencion' => $cerrada,
            'cerrada_at' => $cerrada,
            'ip_profesional' => request()->ip(),
            'user_agent_profesional' => request()->userAgent(),
            'estado' => 'validada_automaticamente',
            'riesgo' => 'bajo',
            'diagnostico' => $data['diagnostico'],
            'observacion' => $data['observacion'] ?: 'Atención simulada cerrada desde el panel profesional.',
            'hash_auditoria' => hash('sha256', $voucher->id.'|'.auth()->id().'|'.$cerrada->toIso8601String().'|'.$data['diagnostico']),
        ]
    );

    $voucher->update([
        'atencion_id' => $atencion->id,
        'profesional_atendio_id' => auth()->user()->profesional_id,
        'atencion_cerrada_at' => $cerrada,
        'ip_profesional' => request()->ip(),
        'validado_at' => $cerrada,
        'estado_validacion' => 'validada_automaticamente',
        'riesgo_validacion' => 'bajo',
        'estado' => 'validado_atencion',
    ]);
    $voucher->agenda->update([
        'estado' => 'atencion_realizada',
        'medichile_estado_id' => $sync['estado_id'],
        'medichile_sincronizado_at' => $sync['sincronizado_at'],
        'medichile_sync_error' => null,
    ]);

    \App\Models\VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'atencion_cerrada_validada_automaticamente',
        'usuario_tipo' => 'profesional',
        'usuario_id' => auth()->id(),
        'descripcion' => 'Atención cerrada con diagnóstico. Validación automática aprobada por identidad del profesional, QR vigente y hora Medichile #'.$sync['hora_medica_id'].' Realizada (ID 6). Bono habilitado para cobro.',
        'ip' => request()->ip(),
    ]);

    return redirect()->route('profesional.cobros')
        ->with('ok', 'Atención cerrada y validada automáticamente. El bono ya está habilitado para cobro.');
}
    // public function finalizarAtencion($id)
    // {
    //     $voucher = Voucher::findOrFail($id);

    //     if (auth()->user()->rol != 'profesional'
    //         || $voucher->profesional_id != auth()->user()->profesional_id) {
    //         abort(403);
    //     }

    //     if (!in_array($voucher->estado, [
    //         'activo',
    //         'asignado',
    //         'en_atencion',
    //         'en atención',
    //     ])) {
    //         return back()->with('error', 'El voucher no está disponible para finalizar.');
    //     }

    //     if (!$voucher->otp_validado_at) {
    //         VoucherAuditoria::create([
    //             'voucher_id' => $voucher->id,
    //             'accion' => 'cobro_rechazado_sin_otp',
    //             'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'api',
    //             'usuario_id' => auth()->id(),
    //             'descripcion' => 'Intento de cobro sin validación OTP',
    //             'ip' => request()->ip(),
    //         ]);

    //         return back()->with('error', 'El cliente aún no ha validado el OTP.');
    //     }

    //     if ($voucher->qr_usado) {
    //         return back()->with('error', 'Este voucher ya fue usado.');
    //     }

    //     $voucher->update([
    //         'estado' => 'cobrado',
    //         'qr_usado' => true,
    //         'qr_usado_at' => now(),
    //         'usado_en' => now(),
    //     ]);

    //     $profesionalId = auth()->user()->profesional_id;

    //     \App\Models\VoucherCobro::create([
    //         'voucher_id' => $voucher->id,
    //         'profesional_id' => $profesionalId,
    //         'veterinario_nombre' => auth()->user()->name,
    //         'sucursal' => 'Sucursal principal',
    //         'monto_cobrado' => $voucher->saldo_veterinario,
    //         'estado' => 'pendiente_rendicion',
    //         'cobrado_en' => now(),
    //     ]);

    //     VoucherAuditoria::create([
    //         'voucher_id' => $voucher->id,
    //         'accion' => 'atencion_finalizada',
    //         'usuario_tipo' => 'profesional',
    //         'usuario_id' => $voucher->profesional_id,
    //         'descripcion' => 'Atención finalizada y voucher enviado a cobro/rendición',
    //         'ip' => request()->ip(),
    //     ]);

    //     return back()->with('ok', 'Atención cerrada y cobro generado.');
    // }

    public function aceptarVoucher(Request $request, $token)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();

        if ($voucher->estado != 'pendiente_cliente') {
            return back();
        }

        $nuevoEstado = $voucher->copago_usuario > 0
            ? 'pendiente_pago'
            : 'activo';
if ($voucher->estado == 'invalidado_cliente') {
    return back()->with('error', 'Este voucher fue invalidado por el cliente.');
}
        $voucher->update([
            'estado' => $nuevoEstado,
            'cliente_aceptado_en' => now(),
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'cliente_acepto',
            'usuario_tipo' => 'cliente',
            'usuario_id' => null,
            'descripcion' => 'Cliente aceptó voucher',
            'ip' => request()->ip(),
        ]);

        return redirect()
            ->route('vouchers.qr', $voucher->qr_token)
            ->with('ok', 'Voucher aceptado.');
    }

    public function rechazarVoucher(Request $request, $token)
    {
        $voucher = Voucher::where('qr_token', $token)->firstOrFail();

        if ($voucher->estado != 'pendiente_cliente') {
            return back();
        }

        $voucher->update([
            'estado' => 'rechazado',
            'cliente_rechazado_en' => now(),
            'motivo_rechazo_cliente' => $request->motivo_rechazo,
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'cliente_rechazo',
            'usuario_tipo' => 'cliente',
            'usuario_id' => null,
            'descripcion' => 'Cliente rechazó voucher',
            'ip' => request()->ip(),
        ]);

        return redirect()
            ->route('vouchers.qr', $voucher->qr_token)
            ->with('ok', 'Voucher rechazado.');
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
            return response()->json([
                'ok' => false,
                'mensaje' => 'OTP expirado',
            ], 400);
        }

        $otpHash = hash('sha256', $request->otp);

        if ($otpHash !== $voucher->otp_hash) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'OTP incorrecto',
            ], 400);
        }
if ($voucher->estado == 'invalidado_cliente') {
    return back()->with('error', 'Este voucher fue invalidado por el cliente.');
}
        $voucher->update([
            'otp_validado_at' => now(),
            'estado' => 'activo',
        ]);

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'otp_validado_cliente',
            'usuario_tipo' => 'cliente',
            'usuario_id' => null,
            'descripcion' => 'Cliente validó OTP y activó voucher',
            'ip' => request()->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Voucher activado correctamente',
            'estado' => $voucher->estado,
        ]);
    $intentos = cache()->get(
    'validar_otp_'.$voucher->id.'_'.request()->ip(),
    0
);

if ($intentos >= 10) {

    $alerta = VoucherAlerta::create([
        'voucher_id' => $voucher->id,
        'tipo_alerta' => 'exceso_intentos_otp',
        'nivel' => 'rojo',
        'descripcion' => 'Demasiados intentos de validación OTP',
    ]);

    $this->notificarAuditor(
        $voucher,
        $alerta,
        'Exceso de intentos OTP',
        'Se detectaron múltiples intentos de validación OTP para el voucher '.$voucher->codigo
    );

    abort(429, 'Demasiados intentos.');
}

cache()->put(
    'validar_otp_'.$voucher->id.'_'.request()->ip(),
    $intentos + 1,
    now()->addMinutes(30)
);


    }
    public function reenviarOtp($token)
{
    $voucher = Voucher::where('qr_token', $token)->firstOrFail();

    if ($voucher->estado == 'cobrado' || $voucher->qr_usado) {
        return back()->with('error', 'No se puede reenviar OTP de un voucher ya cobrado.');
    }

    $otp = random_int(100000, 999999);

    $voucher->update([
        'otp_hash' => hash('sha256', $otp),
        'otp_expira' => now()->addMinutes(10),
        'otp_validado_at' => null,
    ]);

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'otp_reenviado',
        'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'cliente',
        'usuario_id' => auth()->id(),
        'descripcion' => 'OTP reenviado por WhatsApp',
        'ip' => request()->ip(),
    ]);

    $telefono = preg_replace('/[^0-9]/', '', $voucher->cliente_telefono);

    if (substr($telefono, 0, 2) !== '56') {
        $telefono = '56' . ltrim($telefono, '0');
    }

   $mensaje =
"SDI\n\n".
"Tu código para activar el voucher {$voucher->codigo} es: {$otp}\n".
"Vence en 10 minutos.\n\n".
"Si NO reconoces este voucher, invalídalo aquí:\n".
route('vouchers.invalidar', $voucher->qr_token);

    $whatsappUrl = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);

    return redirect()
        ->route('vouchers.show', $voucher->id)
        ->with('whatsapp_otp_url', $whatsappUrl)
        ->with('ok', 'Nuevo OTP generado. Envíalo al cliente por WhatsApp.');

   $intentos = cache()->get(
    'otp_'.$voucher->id.'_'.request()->ip(),
    0
);

if ($intentos >= 5) {
    abort(429, 'Demasiados intentos. Espere 15 minutos.');
}

cache()->put(
    'otp_'.$voucher->id.'_'.request()->ip(),
    $intentos + 1,
    now()->addMinutes(15)
);
}

    public function invalidarCliente($token)
{
    $voucher = Voucher::where(
        'qr_token',
        $token
    )->firstOrFail();

    if (
        $voucher->estado == 'cobrado' ||
        $voucher->copago_devuelto
    ) {

        return back()->with(
            'error',
            'Voucher ya procesado.'
        );
    }

    $voucher->update([

        'estado' => 'invalidado_cliente',

        'qr_usado' => true,

        'qr_usado_at' => now(),

        'invalidado_en' => now(),

        'motivo_invalidacion' =>
            'Invalidado por cliente desde WhatsApp',

        'copago_devuelto' => true,
    ]);

    ClienteSaldo::create([

        'voucher_id' => $voucher->id,

        'cliente_nombre' =>
            $voucher->cliente_nombre,

        'cliente_rut_hash' =>
            $voucher->cliente_rut_hash,

        'monto' =>
            $voucher->copago_usuario,

        'origen' =>
            'voucher_invalidado',

        'estado' =>
            'disponible',

        'descripcion' =>
            'Saldo generado por invalidación de voucher',

    ]);
if ($voucher->copago_usuario >= 20000) {

    $alerta = \App\Models\VoucherAlerta::create([
    'voucher_id' => $voucher->id,
    'tipo_alerta' => 'invalidacion_copago_alto',
    'nivel' => 'rojo',
    'descripcion' => 'Cliente invalidó voucher con copago alto. Revisar posible fraude o error de emisión.',
]);

$this->notificarAuditor(
    $voucher,
    $alerta,
    'Invalidación con copago alto',
    'El voucher '.$voucher->codigo.' fue invalidado con copago de $'.number_format($voucher->copago_usuario, 0, ',', '.')
);

}
$invalidacionesCliente = Voucher::where('cliente_rut_hash', $voucher->cliente_rut_hash)
    ->where('estado', 'invalidado_cliente')
    ->count();

if ($invalidacionesCliente >= 3) {

  $alerta = \App\Models\VoucherAlerta::create([
    'voucher_id' => $voucher->id,
    'tipo_alerta' => 'invalidacion_copago_alto',
    'nivel' => 'rojo',
    'descripcion' => 'Cliente invalidó voucher con copago alto. Revisar posible fraude o error de emisión.',
]);

$this->notificarAuditor(
    $voucher,
    $alerta,
    'Invalidación con copago alto',
    'El voucher '.$voucher->codigo.' fue invalidado con copago de $'.number_format($voucher->copago_usuario, 0, ',', '.')
);
    if ($voucher->profesional_id) {

    $invalidacionesProfesional = Voucher::where('profesional_id', $voucher->profesional_id)
        ->where('estado', 'invalidado_cliente')
        ->count();

    if ($invalidacionesProfesional >= 5) {

       $alerta = \App\Models\VoucherAlerta::create([
    'voucher_id' => $voucher->id,
    'tipo_alerta' => 'invalidacion_copago_alto',
    'nivel' => 'rojo',
    'descripcion' => 'Cliente invalidó voucher con copago alto. Revisar posible fraude o error de emisión.',
]);

$this->notificarAuditor(
    $voucher,
    $alerta,
    'Invalidación con copago alto',
    'El voucher '.$voucher->codigo.' fue invalidado con copago de $'.number_format($voucher->copago_usuario, 0, ',', '.')
);

    }
}
    $invalidacionesVendedor = Voucher::where('vendedor_id', $voucher->vendedor_id)
    ->where('estado', 'invalidado_cliente')
    ->count();

if ($invalidacionesVendedor >= 5) {

   $alerta = \App\Models\VoucherAlerta::create([
    'voucher_id' => $voucher->id,
    'tipo_alerta' => 'invalidacion_copago_alto',
    'nivel' => 'rojo',
    'descripcion' => 'Cliente invalidó voucher con copago alto. Revisar posible fraude o error de emisión.',
]);

$this->notificarAuditor(
    $voucher,
    $alerta,
    'Invalidación con copago alto',
    'El voucher '.$voucher->codigo.' fue invalidado con copago de $'.number_format($voucher->copago_usuario, 0, ',', '.')
);

}

}

    VoucherAuditoria::create([

        'voucher_id' => $voucher->id,

        'accion' => 'voucher_invalidado_cliente',

        'usuario_tipo' => 'cliente',

        'descripcion' =>
            'Cliente invalidó voucher desde enlace WhatsApp',

        'ip' => request()->ip(),
    ]);

    return view(
        'vouchers.invalidado',
        compact('voucher')
    );
    if ($voucher->estado == 'cobrado' || $voucher->qr_usado || $voucher->copago_devuelto) {
    return view('vouchers.invalidado', compact('voucher'));
}
if ($voucher->copago_usuario > 0) {

    ClienteSaldo::create([
        'voucher_id' => $voucher->id,
        'cliente_nombre' => $voucher->cliente_nombre,
        'cliente_rut_hash' => $voucher->cliente_rut_hash,
        'monto' => $voucher->copago_usuario,
        'origen' => 'voucher_invalidado',
        'estado' => 'disponible',
        'descripcion' => 'Saldo generado por invalidación de voucher',
    ]);

}

}
private function normalizarRut($rut): string
{
    return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
}

private function decryptValue($value): ?string
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

private function encryptOptional($value): ?string
{
    if (! filled($value)) {
        return null;
    }

    return Crypt::encryptString((string) $value);
}

private function edadDesdeFecha(?string $fecha): ?int
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

private function notificarAuditor($voucher, $alerta, $titulo, $mensaje)
{
    \App\Models\AuditorNotificacion::create([
        'voucher_id' => $voucher->id,
        'alerta_id' => $alerta->id,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
    ]);

    VoucherAuditoria::create([
        'voucher_id' => $voucher->id,
        'accion' => 'notificacion_auditor_creada',
        'usuario_tipo' => 'sistema',
        'usuario_id' => null,
        'descripcion' => $titulo,
        'ip' => request()->ip(),
    ]);
}

public function validarAtencionWeb($id)
{
    $voucher = Voucher::findOrFail($id);

    $atencion = \App\Models\VoucherAtencion::where('voucher_id', $voucher->id)->first();

    if (!$atencion || $atencion->estado !== 'cerrada_por_profesional') {
        return back()->with('error', 'La atención no está cerrada por profesional.');
    }

    if ($atencion->asistente_id || $atencion->estado === 'validada_por_asistente') {
        return back()->with('error', 'La atención ya fue validada.');
    }

    $asistenteId = auth()->id();

    $riesgo = 'bajo';

    if ((int) $atencion->profesional_id === (int) $asistenteId) {
        $riesgo = 'alto';
    }

    if ($atencion->ip_profesional && $atencion->ip_profesional === request()->ip()) {
        $riesgo = $riesgo === 'alto' ? 'alto' : 'medio';
    }

    $hashAuditoria = hash('sha256', implode('|', [
        $voucher->id,
        $atencion->id,
        $atencion->profesional_id,
        $asistenteId,
        $atencion->cerrada_at,
        now(),
        $atencion->ip_profesional,
        request()->ip(),
    ]));

    $atencion->update([
        'asistente_id' => $asistenteId,
        'validada_at' => now(),
        'ip_asistente' => request()->ip(),
        'user_agent_asistente' => request()->userAgent(),
        'estado' => 'validada_por_asistente',
        'riesgo' => $riesgo,
        'hash_auditoria' => $hashAuditoria,
    ]);

    $voucher->update([
        'agenda_id' => $atencion->agenda_id,
        'atencion_id' => $atencion->id,
        'profesional_atendio_id' => $atencion->profesional_id,
        'asistente_valido_id' => $asistenteId,
        'atencion_cerrada_at' => $atencion->cerrada_at,
        'validado_at' => now(),
        'ip_profesional' => $atencion->ip_profesional,
        'ip_asistente' => request()->ip(),
        'estado_validacion' => 'validada_por_asistente',
        'riesgo_validacion' => $riesgo,
        'estado' => 'validado_atencion',
    ]);

    return back()->with('ok', 'Atención validada por asistente. Bono habilitado para que el profesional solicite el cobro. Riesgo: '.$riesgo);
}

public function confirmarInvalidacion($token)
{
    $voucher = Voucher::where('qr_token', $token)->firstOrFail();

    return view('vouchers.confirmar_invalidacion', compact('voucher'));
}
}
