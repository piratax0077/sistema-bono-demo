<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! in_array($user->rol, ['admin', 'auditor'], true)) {
            return $next($request);
        }

        if (app()->environment('local') && in_array($user->email, [
            'administrador@gmail.com',
            'contralor@gmail.com',
        ], true)) {
            return $next($request);
        }

        if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at) {
            return redirect()->guest(route('two-factor.setup'));
        }

        if (! $request->session()->get('two_factor_verified', false)) {
            return redirect()->guest(route('two-factor.challenge'));
        }

        return $next($request);
    }
}
