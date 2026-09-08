<?php

namespace App\Http\Controllers;

use App\Models\LoginAuditoria;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\TotpService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    public function setup(Request $request, TotpService $totp)
    {
        $user = $request->user();

        if (! $this->requiresTwoFactor($user)) {
            return redirect(RouteServiceProvider::HOME);
        }

        if (! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => $totp->generateSecret(),
            ])->save();
        }

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.challenge');
        }

        return view('auth.two_factor_setup', [
            'secret' => $user->two_factor_secret,
        ]);
    }

    public function qr(Request $request, TotpService $totp)
    {
        $user = $request->user();

        abort_unless($this->requiresTwoFactor($user) && $user->two_factor_secret, 404);

        $uri = $totp->provisioningUri('SDI Salud Digital Integrada', $user->email, $user->two_factor_secret);

        return response(
            QrCode::format('svg')->size(280)->margin(2)->generate($uri),
            200
        )->header('Content-Type', 'image/svg+xml');
    }

    public function challenge(Request $request)
    {
        $user = $request->user();

        if (! $this->requiresTwoFactor($user)) {
            return redirect(RouteServiceProvider::HOME);
        }

        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two_factor_challenge');
    }

    public function confirm(Request $request, TotpService $totp)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ]);

        $user = $request->user();

        abort_unless($this->requiresTwoFactor($user) && $user->two_factor_secret, 403);

        if (! $totp->verify($user->two_factor_secret, $request->input('code'))) {
            $this->audit($user, 'totp_fallido', $request);
            return back()->withErrors(['code' => 'Codigo de autenticacion invalido o vencido.'])->onlyInput('code');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at ?: now(),
            'two_factor_last_verified_at' => now(),
        ])->save();

        $request->session()->put('two_factor_verified', true);
        $request->session()->put('two_factor_verified_at', now()->timestamp);

        $this->audit($user, 'totp_exitoso', $request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function reset(Request $request, $id)
    {
        abort_unless($request->user() && $request->user()->rol === 'admin', 403);

        $user = User::findOrFail($id);

        abort_unless($this->requiresTwoFactor($user), 422);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_verified_at' => null,
        ])->save();

        $this->audit($user, 'totp_reset_admin_'.$request->user()->id, $request);

        return back()->with('ok', 'Autenticador reiniciado. En el proximo login debera escanear un nuevo QR.');
    }

    private function requiresTwoFactor($user)
    {
        return $user && in_array($user->rol, ['admin', 'auditor'], true);
    }

    private function audit(User $user, $result, Request $request)
    {
        LoginAuditoria::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'resultado' => $result,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
