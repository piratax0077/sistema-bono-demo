<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (auth()->user()->activo == 0) {
            auth()->logout();

            return redirect('/login')
                ->withErrors([
                    'email' => 'Usuario inactivo. Contacte al administrador.'
                ]);
        }

        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
