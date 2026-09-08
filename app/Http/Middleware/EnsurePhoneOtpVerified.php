<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneOtpVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->rol !== 'cliente') {
            return $next($request);
        }

        if (! $request->session()->get('phone_otp_verified', false)) {
            return redirect()->guest(route('login.otp'));
        }

        return $next($request);
    }
}
