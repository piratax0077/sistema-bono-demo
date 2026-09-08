<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\Totem;
use App\Models\TotemVenta;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardTotemController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        $totalVentas = TotemVenta::count();

        $ventasPagadas = TotemVenta::where('estado', 'pagado')->count();

        $ventasPendientes = TotemVenta::where('estado', 'pendiente')->count();

        $tasaConversion = $totalVentas > 0
            ? round(($ventasPagadas * 100) / $totalVentas, 2)
            : 0;

        return response()->json([

            'ok' => true,

            'resumen' => [
                'totems_activos' => Totem::where('activo', true)->count(),
                'totems_alerta' => Totem::where('estado_operacional', 'alerta')->count(),

                'ventas_hoy' => TotemVenta::whereDate(
                    'created_at',
                    $hoy
                )->count(),

                'monto_hoy' => TotemVenta::whereDate(
                    'created_at',
                    $hoy
                )->sum('total'),

                'vouchers_emitidos' => Voucher::whereDate(
                    'created_at',
                    $hoy
                )->count(),

                'clientes_identificados' => TotemVenta::whereDate(
                    'created_at',
                    $hoy
                )->whereNotNull('cliente_id')->count(),

                'clientes_invitados' => TotemVenta::whereDate(
                    'created_at',
                    $hoy
                )->whereNull('cliente_id')->count(),

                'clientes_nuevos_hoy' => User::whereDate(
                    'created_at',
                    $hoy
                )->count(),

                'ventas_pagadas' => $ventasPagadas,

                'ventas_pendientes' => $ventasPendientes,

                'tasa_conversion' => $tasaConversion,
            ],

            'top_prestadores' => TotemVenta::select(
                    'prestador_nombre',
                    DB::raw('COUNT(*) as ventas'),
                    DB::raw('SUM(total) as monto')
                )
                ->whereNotNull('prestador_nombre')
                ->groupBy('prestador_nombre')
                ->orderByDesc('ventas')
                ->take(10)
                ->get(),

            'top_clientes' => TotemVenta::select(
                    'cliente_id',
                    'cliente_nombre',
                    DB::raw('COUNT(*) as compras'),
                    DB::raw('SUM(total) as monto')
                )
                ->whereNotNull('cliente_id')
                ->groupBy('cliente_id', 'cliente_nombre')
                ->orderByDesc('compras')
                ->take(10)
                ->get(),

            'totems' => Totem::select(
                    'id',
                    'codigo',
                    'nombre',
                    'activo',
                    'ubicacion',
                    'ultimo_ping',
                    'estado_operacional',
                    'geolocalizacion_lat',
                    'geolocalizacion_lng',
                    'ultima_alerta_at',
                    'ultima_alerta_mensaje'
                )
                ->get(),

            'ultimas_ventas' => TotemVenta::latest()
                ->take(10)
                ->get([
                    'id',
                    'totem_id',
                    'cliente_nombre',
                    'total',
                    'estado',
                    'created_at'
                ]),
        ]);
    }
}
