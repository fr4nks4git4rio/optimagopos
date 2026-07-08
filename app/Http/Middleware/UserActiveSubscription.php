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
        // 1. Si el usuario tiene una sesión temporal de 2FA activa...
        Log::info(user()->cliente_id);
        if (user()->cliente_id && user()->suscripciones_activas()->count() == 0) {
            return redirect()->to('/')->withErrors(['email' => __('auth.subscription_failed')]);
        }

        return $next($request);
    }
}
