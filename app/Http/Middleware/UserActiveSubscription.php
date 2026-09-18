<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserActiveSubscription
{
    /**
     * Manejar una petición entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $user = user()) {
            return $next($request);
        }

        if ($user->hasAnyRole(['Admin', 'Manager']) && $user->suscripciones_activas()->count() == 0) {
            // Redirigir a una ruta del grupo 'guest' (/ , /login) provoca un redirect
            // loop: /home -> / -> RedirectIfAuthenticated -> /home -> ...
            // Cerramos sesion y volvemos al login para romper el ciclo.
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => __('auth.subscription_failed')]);
        }

        return $next($request);
    }
}
