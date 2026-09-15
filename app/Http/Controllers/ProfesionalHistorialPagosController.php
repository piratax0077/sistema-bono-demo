<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherCobro;

class ProfesionalHistorialPagosController extends Controller
{
    public function index()
    {
        $profesionalId = auth()->user()->profesional_id;
        $vouchers = Voucher::with(['agenda', 'pagos', 'cobros.rendicion.liquidaciones'])
            ->where(function ($query) use ($profesionalId) {
                $query->where('profesional_id', $profesionalId)
                    ->orWhereNotNull('prestador_nombre');
            })
            ->latest('id')
            ->get();

        $cobros = VoucherCobro::with(['voucher', 'rendicion.liquidaciones'])
            ->where('profesional_id', $profesionalId)
            ->latest('id')
            ->get();

        $resumen = [
            'bonos' => $vouchers->count(),
            'pagos_registrados' => $vouchers->filter(fn ($voucher) => $voucher->pagos->isNotEmpty())->count(),
            'en_auditoria' => $cobros->whereIn('estado', ['pendiente_auditoria', 'observado_auditoria'])->count(),
            'depositados' => $cobros->where('pago_estado', 'depositado')->count(),
        ];

        return view('profesional.historial_pagos', compact('vouchers', 'resumen'));
    }
}
