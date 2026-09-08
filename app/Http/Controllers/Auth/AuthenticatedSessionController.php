<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\IpAutorizada;
use App\Models\LoginAuditoria;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->activo) {
            $this->audit($user, 'rechazado_usuario_inactivo', $request);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Usuario inactivo. Contacte al administrador.',
            ]);
        }

        if ($this->isReviewDemoUser($user)) {
            $request->session()->put([
                'phone_otp_verified' => true,
                'phone_otp_verified_at' => now()->timestamp,
                'two_factor_verified' => true,
                'two_factor_verified_at' => now()->timestamp,
            ]);
            $this->audit($user, 'exitoso_demo_revision', $request);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if ($this->requiresSecondFactor($user)) {
            if (! $this->ipIsAllowed($user, $request)) {
                $this->audit($user, 'ip_no_autorizada', $request);
                Auth::guard('web')->logout();

                return redirect('/login')->withErrors([
                    'email' => 'IP no autorizada para este perfil.',
                ]);
            }

            $request->session()->forget([
                'two_factor_verified',
                'two_factor_verified_at',
                'login_otp_user_id',
                'login_otp_pending',
                'phone_otp_verified',
                'phone_otp_verified_at',
            ]);

            $this->audit($user, 'totp_pendiente', $request);

            if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at) {
                return redirect()->route('two-factor.setup');
            }

            return redirect()->route('two-factor.challenge');
        }

        if ($this->requiresPhoneOtp($user)) {
            if (! $user->telefono) {
                $this->audit($user, 'telefono_no_registrado', $request);
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors([
                    'email' => 'El beneficiario debe tener telefono registrado para autenticar.',
                ]);
            }

            $request->session()->forget([
                'two_factor_verified',
                'two_factor_verified_at',
                'phone_otp_verified',
                'phone_otp_verified_at',
            ]);

            $flash = app(PhoneOtpController::class)->issueOtpFor($user, $request);
            $this->audit($user, 'telefono_otp_pendiente', $request);

            return redirect()
                ->route('login.otp')
                ->with($flash);
        }

        $this->audit($user, 'exitoso', $request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request)
    {
        if ($request->user()) {
            $this->audit($request->user(), 'logout', $request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function requiresSecondFactor(User $user): bool
    {
        return in_array($user->rol, ['admin', 'auditor'], true);
    }

    private function isReviewDemoUser(User $user): bool
    {
        return app()->environment('local')
            && (config('security.review_demo_auth_bypass', false) || app()->environment('local'))
            && in_array($user->email, [
                'paciente@gmail.com',
                'profesional@gmail.com',
                'asistente@gmail.com',
                'administrador@gmail.com',
                'contralor@gmail.com',
            ], true);
    }

    private function requiresPhoneOtp(User $user): bool
    {
        return $user->rol === 'cliente';
    }

    private function ipIsAllowed(User $user, Request $request): bool
    {
        if (! config('security.ip_whitelist_enabled') || ! $this->requiresSecondFactor($user)) {
            return true;
        }

        return IpAutorizada::where('activo', true)
            ->where('ip', $request->ip())
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('rol', $user->rol);
            })
            ->exists();
    }

    private function audit(User $user, string $result, Request $request): void
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
