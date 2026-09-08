<?php

namespace App\Http\Controllers;
use App\Models\VoucherCobro;
use App\Models\VoucherRendicion;
use App\Models\VoucherLiquidacion;
use App\Models\VoucherProfesional;
use App\Models\VoucherAuditoria;
use App\Models\PagoAutorizacion;

use Illuminate\Support\Str;

class RendicionController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->rol != 'admin') {
            abort(403);
        }

        $cobrosPendientes = VoucherCobro::with('voucher')
            ->whereNull('voucher_rendicion_id')
            ->where('estado', 'pendiente_rendicion')
            ->whereHas('voucher', function ($q) {
                $q->where('estado', 'validado_atencion');
            })
            ->get();

        $rendiciones = VoucherRendicion::orderBy('id', 'desc')->get();

        return view('rendiciones.index', compact('cobrosPendientes', 'rendiciones'));
    }

    public function generar()
    {
        $cobros = VoucherCobro::with('voucher')
            ->whereNull('voucher_rendicion_id')
            ->where('estado', 'pendiente_rendicion')
            ->whereHas('voucher', function ($q) {
                $q->where('estado', 'validado_atencion');
            })
            ->get();

        if ($cobros->count() == 0) {
            return back()->with('error', 'No hay cobros pendientes con atención validada.');
        }

        $rendicion = VoucherRendicion::create([
            'veterinario_nombre' => 'Veterinaria Demo',
            'sucursal' => 'Sucursal Centro',
            'total_cobrado' => $cobros->sum('monto_cobrado'),
            'cantidad_vouchers' => $cobros->count(),
            'estado' => 'pendiente',
            'rendida_en' => now(),
        ]);

        foreach ($cobros as $cobro) {
            $cobro->update([
                'voucher_rendicion_id' => $rendicion->id,
                'estado' => 'rendido',
            ]);
        }

        return back()->with('ok', 'Rendición generada correctamente.');
    }

    public function generarLiquidacion($id)
    {
        $rendicion = VoucherRendicion::with('cobros.voucher')->findOrFail($id);
        foreach ($rendicion->cobros as $cobro) {

            if (!$cobro->voucher) {
                return back()->with('error', 'Liquidación bloqueada: cobro sin voucher asociado.');
            }

            $atencion = \App\Models\VoucherAtencion::where('voucher_id', $cobro->voucher_id)->first();

            if (!$atencion || $atencion->estado !== 'validada_por_asistente') {
                return back()->with('error', 'Liquidación bloqueada: voucher sin atención validada por asistente.');
            }

            if (in_array($atencion->riesgo, ['alto'])) {
                return back()->with('error', 'Liquidación bloqueada: existe atención con riesgo alto.');
            }
        }

        $primerCobro = $rendicion->cobros()->first();

        $profesional = null;

        if ($primerCobro && $primerCobro->profesional_id) {
            $profesional = VoucherProfesional::find($primerCobro->profesional_id);
        }

        $comision = $rendicion->cobros->sum(function ($cobro) {
            return $cobro->voucher->comision_veterchile ?? 0;
        });

        $liquidacion = VoucherLiquidacion::create([

            'voucher_rendicion_id' => $rendicion->id,
            'profesional_id' => $primerCobro->profesional_id ?? null,
            'profesional_nombre' => $primerCobro->veterinario_nombre ?? null,
            'banco' => $profesional->banco ?? null,
            'tipo_cuenta' => $profesional->tipo_cuenta ?? null,
            'numero_cuenta' => $profesional->numero_cuenta ?? null,
            'monto_profesional' => $rendicion->total_cobrado,
            'comision_veterchile' => $comision,
            'estado' => 'pendiente_autorizacion_profesional',

        ]);
        $token = (string) Str::uuid();

        PagoAutorizacion::create([

            'voucher_liquidacion_id' => $liquidacion->id,

            'voucher_rendicion_id' => $rendicion->id,

            'profesional_id' => $liquidacion->profesional_id,

            'token' => $token,

            'estado' => 'pendiente',

            'ip_solicitud' => request()->ip(),

            'expira_at' => now()->addHours(24),

        ]);
        $rendicion->update([

            'estado' => 'pendiente_autorizacion_profesional',

        ]);
        return back()->with(
            'ok',
            'Liquidación creada y enviada al profesional para autorización.'
        );
        // return back()->with('ok', 'Liquidación generada correctamente.');



    }

    public function pagarLiquidacion(Request $request, $id)
    {
        $liquidacion = VoucherLiquidacion::findOrFail($id);
        $rendicion = $liquidacion->rendicion;

        foreach ($rendicion->cobros as $cobro) {

            if (!$cobro->voucher) {
                return back()->with('error', 'Pago bloqueado: cobro sin voucher asociado.');
            }

            $atencion = \App\Models\VoucherAtencion::where('voucher_id', $cobro->voucher_id)->first();

            if (!$atencion || $atencion->estado !== 'validada_por_asistente') {
                return back()->with('error', 'Pago bloqueado: voucher sin atención validada por asistente.');
            }

            if ($atencion->riesgo === 'alto') {
                return back()->with('error', 'Pago bloqueado: existe una atención con riesgo alto.');
            }

            $alertasRojas = \App\Models\VoucherAlerta::where('voucher_id', $cobro->voucher_id)
                ->where('nivel', 'rojo')
                ->where('resuelta', false)
                ->count();

            if ($alertasRojas > 0) {
                return back()->with('error', 'Pago bloqueado: existen alertas rojas pendientes.');
            }
        }

        $liquidacion->update([
            'estado' => 'pagado',
            'medio_pago' => $request->medio_pago ?? 'transferencia',
            'comprobante_transferencia' => $request->comprobante_transferencia,
            'pagado_en' => now(),
        ]);

        VoucherAuditoria::create([
            'accion' => 'liquidacion_pagada',
            'usuario_tipo' => 'admin',
            'usuario_id' => auth()->id() ?? 1,
            'descripcion' => 'Liquidación pagada al profesional '.$liquidacion->profesional_nombre,
            'ip' => request()->ip(),
        ]);

        return back()->with('ok', 'Liquidación marcada como pagada.');
    }
}
