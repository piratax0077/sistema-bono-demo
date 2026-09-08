<?php

namespace App\Http\Controllers;

use App\Models\Voucher;

class DemoFlujoBonoController extends Controller
{
    public function circuitoCobranza()
    {
        $vouchers = Voucher::where('codigo', 'like', 'DEMO-COBRO-%')
            ->with(['cliente', 'profesional', 'pagos', 'cobros.rendicion.liquidaciones', 'atencion'])
            ->orderBy('codigo')->get();
        abort_if($vouchers->isEmpty(), 404);
        $rendicion = optional($vouchers->first()->cobros->first())->rendicion;
        $auditorias = \App\Models\VoucherAuditoria::whereIn('voucher_id', $vouchers->pluck('id'))->orderBy('id')->get();

        return view('demo.circuito_cobranza', compact('vouchers', 'rendicion', 'auditorias'));
    }

    public function index()
    {
        $voucher = Voucher::where('codigo', 'DEMO-FLUJO-001')
            ->with(['cliente', 'profesional', 'pagos', 'cobros.rendicion.liquidaciones', 'atencion'])
            ->firstOrFail();

        $entregas = $voucher->hasMany(\App\Models\VoucherDeliveryRequest::class)->latest()->get();
        $auditorias = \App\Models\VoucherAuditoria::where('voucher_id', $voucher->id)->orderBy('id')->get();

        return view('demo.flujo_bono', compact('voucher', 'entregas', 'auditorias'));
    }
}
