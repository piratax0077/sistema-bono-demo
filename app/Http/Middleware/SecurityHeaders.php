<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=(self)');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; " .
            "img-src 'self' data:; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.datatables.net https://fonts.googleapis.com; " .
            "script-src 'self' 'unsafe-inline' https://cdn.datatables.net; " .
            "connect-src 'self' " . config('security.totem_origin')
        );

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        if ($request->is('api/*') || auth()->check()) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
