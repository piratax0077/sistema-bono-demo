<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherCobro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DemoPortalController extends Controller
{
    public function loginBono(Request $request)
    {
        abort_unless(config('demo.enabled') && config('demo.user_switch_enabled'), 404);
        $user = User::where('email', 'paciente@gmail.com')->where('activo', true)->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('two_factor_verified', true);
        $request->session()->put('phone_otp_verified', true);

        Log::info('Ingreso automático demo-bono', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return redirect()->route('home')
            ->with('ok', 'Sesión demo-bono autenticada automáticamente.');
    }

    public function index(Request $request)
    {
        abort_unless(config('demo.enabled'), 404);

        if (! Auth::check() && config('demo.user_switch_enabled')) {
            return $this->loginBono($request);
        }
        if (Auth::user()?->rol === 'cliente') {
            return redirect()->route('paciente.home');
        }

        $vouchers = Voucher::with(['agenda', 'atencion', 'cobros.auditor', 'cobros.decisorPago'])
            ->latest('id')->take(50)->get();
        $resumen = [
            'comprados' => Voucher::count(),
            'en_espera' => Voucher::whereHas('agenda', fn ($q) => $q->where('estado', 'paciente_en_espera'))->count(),
            'atendidos' => Voucher::whereNotNull('atencion_cerrada_at')->count(),
            'en_auditoria' => VoucherCobro::whereIn('estado', ['pendiente_auditoria', 'observado_auditoria'])->count(),
            'autorizados' => VoucherCobro::where('estado', 'pendiente_rendicion')->count(),
            'depositados' => VoucherCobro::where('pago_estado', 'depositado')->count(),
        ];

        return view('home', compact('resumen'));
    }

    public function switchUser(Request $request, string $perfil)
    {
        abort_unless(config('demo.enabled') && config('demo.user_switch_enabled'), 404);
        $perfilConfig = config('demo.users.'.$perfil);
        abort_unless(is_array($perfilConfig), 404);

        $user = User::where('email', $perfilConfig['email'])->where('activo', true)->firstOrFail();
        $anterior = Auth::user()?->email;
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('two_factor_verified', true);
        $request->session()->put('phone_otp_verified', true);

        Log::info('Cambio de perfil del portal demo', [
            'anterior' => $anterior,
            'nuevo' => $user->email,
            'ip' => $request->ip(),
        ]);

        $destinosPaciente = ['home', 'paciente.totem', 'paciente.escritorio', 'paciente.agenda'];
        $destino = (string) $request->input('destino', '');
        if ($perfil === 'paciente' && in_array($destino, $destinosPaciente, true)) {
            return redirect()->route($destino)->with('ok', 'Perfil cambiado a Paciente.');
        }

        $destinosProfesional = ['profesional.escritorio', 'profesional.cobros', 'profesional.historial_pagos'];
        if ($perfil === 'profesional' && in_array($destino, $destinosProfesional, true)) {
            return redirect()->route($destino)->with('ok', 'Perfil cambiado a Profesional.');
        }

        $destinosContraloria = [
            'auditoria.index',
            'auditoria.trazabilidad',
            'auditoria.notificaciones',
            'auditoria.logins',
        ];
        if ($perfil === 'contralor' && in_array($destino, $destinosContraloria, true)) {
            return redirect()->route($destino)->with('ok', 'Perfil cambiado a Contraloría.');
        }

        if (! empty($perfilConfig['route'])) {
            return redirect()->route($perfilConfig['route'])->with('ok', 'Perfil cambiado a '.$perfilConfig['label'].'.');
        }

        return redirect($perfilConfig['url'])->with('ok', 'Perfil cambiado a '.$perfilConfig['label'].'.');
    }
}
