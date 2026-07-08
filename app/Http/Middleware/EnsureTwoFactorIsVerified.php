<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsVerified
{
    /**
     * Manejar una petición entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si el usuario tiene una sesión temporal de 2FA activa...
        if ($request->session()->has('two_factor_user_id')) {

            // ... y está intentando acceder a cualquier ruta que NO sea la de verificación 2FA, lo redirigimos.
            if (! $request->routeIs('auth.two-factor')) {
                return redirect()->route('auth.two-factor');
            }
        }

        return $next($request);
    }
}
