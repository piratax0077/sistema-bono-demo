<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\LoginAuditoria;
use App\Models\AuditorNotificacion;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   public function authenticate()
{
    $this->ensureIsNotRateLimited();

    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {

        RateLimiter::hit($this->throttleKey());

        LoginAuditoria::create([
            'user_id' => null,
            'email' => $this->input('email'),
            'resultado' => 'fallido',
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
public function ensureIsNotRateLimited()
{
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
        return;
    }

    event(new Lockout($this));

    $seconds = RateLimiter::availableIn($this->throttleKey());

    LoginAuditoria::create([
        'user_id' => null,
        'email' => $this->input('email'),
        'resultado' => 'bloqueado_rate_limit',
        'ip' => $this->ip(),
        'user_agent' => $this->userAgent(),
    ]);
    $fallosIp = LoginAuditoria::where('ip', $this->ip())
    ->where('resultado', 'fallido')
    ->where('created_at', '>=', now()->subMinutes(30))
    ->count();
    $fallosEmail = LoginAuditoria::where('email', $this->input('email'))
    ->where('resultado', 'fallido')
    ->where('created_at', '>=', now()->subMinutes(30))
    ->count();

if ($fallosEmail >= 5) {

    AuditorNotificacion::create([
        'voucher_id' => null,
        'alerta_id' => null,
        'titulo' => 'Email con múltiples intentos fallidos',
        'mensaje' => 'El email '.$this->input('email').' registra '.$fallosEmail.' intentos fallidos en los últimos 30 minutos.',
    ]);

}
if ($fallosIp >= 10) {

    AuditorNotificacion::create([
        'voucher_id' => null,
        'alerta_id' => null,
        'titulo' => 'IP sospechosa en login',
        'mensaje' => 'La IP '.$this->ip().' registra '.$fallosIp.' intentos fallidos de login en los últimos 30 minutos.',
    ]);

}
    AuditorNotificacion::create([
        'voucher_id' => null,
        'alerta_id' => null,
        'titulo' => 'Bloqueo por intentos de login',
        'mensaje' => 'Se bloqueó temporalmente el email '.$this->input('email').' desde la IP '.$this->ip().' por demasiados intentos de acceso.',
    ]);

    throw ValidationException::withMessages([
        'email' => trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]),
    ]);
}

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
