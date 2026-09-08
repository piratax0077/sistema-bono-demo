<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAuditoria;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class PhoneOtpController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->rol === 'cliente', 403);

        if ($request->session()->get('phone_otp_verified', false)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return view('auth.login_otp', [
            'telefono' => $this->maskedPhone($user->telefono),
            'expira' => $user->login_otp_expira,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = $request->user();
        abort_unless($user && $user->rol === 'cliente', 403);

        if (! $user->login_otp_hash || ! $user->login_otp_expira || now()->gt($user->login_otp_expira)) {
            $this->audit($user, 'telefono_otp_expirado', $request);

            return back()
                ->withErrors(['otp' => 'El codigo vencio. Solicita uno nuevo.'])
                ->withInput();
        }

        if (! hash_equals((string) $user->login_otp_hash, hash('sha256', (string) $request->otp))) {
            $this->audit($user, 'telefono_otp_fallido', $request);

            return back()
                ->withErrors(['otp' => 'Codigo incorrecto.'])
                ->withInput();
        }

        $user->update([
            'login_otp_hash' => null,
            'login_otp_expira' => null,
            'login_otp_validado_at' => now(),
        ]);

        $request->session()->put('phone_otp_verified', true);
        $request->session()->put('phone_otp_verified_at', now()->timestamp);
        $request->session()->forget(['login_otp_pending', 'login_otp_user_id']);

        $this->audit($user, 'telefono_otp_exitoso', $request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->rol === 'cliente', 403);

        $otp = $this->generateOtp($user, $request);

        return back()->with($this->demoPayload($otp));
    }

    public function issueOtpFor($user, Request $request): array
    {
        $otp = $this->generateOtp($user, $request);

        return $this->demoPayload($otp);
    }

    private function generateOtp($user, Request $request): string
    {
        $otp = (string) random_int(100000, 999999);

        $user->update([
            'login_otp_hash' => hash('sha256', $otp),
            'login_otp_expira' => now()->addMinutes(5),
            'login_otp_validado_at' => null,
        ]);

        $request->session()->put('login_otp_pending', true);
        $request->session()->put('login_otp_user_id', $user->id);
        $request->session()->forget('phone_otp_verified');

        $this->audit($user, 'telefono_otp_enviado', $request);

        return $otp;
    }

    private function demoPayload(string $otp): array
    {
        if (app()->environment(['local', 'testing'])) {
            return [
                'otp_demo' => $otp,
                'ok' => 'Codigo telefonico generado. En produccion se envia por WhatsApp/SMS.',
            ];
        }

        return [
            'ok' => 'Codigo enviado al telefono registrado.',
        ];
    }

    private function maskedPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return 'telefono no registrado';
        }

        return str_repeat('*', max(strlen($digits) - 4, 0)).substr($digits, -4);
    }

    private function audit($user, string $result, Request $request): void
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
