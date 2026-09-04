<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Desactiva el activitylog de Spatie solo durante el request.
 *
 * La ingesta POS crea 1 ticket + N productos/operaciones/impuestos por request;
 * con LogsActivity en esos modelos cada escritura generaba un INSERT extra en
 * activity_log (~2x escrituras). El log de negocio se mantiene en UI.
 * Se reactiva en terminate() para no contaminar workers de cola (long-lived).
 */
class DisableActivityLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        activity()->disableLogging();

        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        activity()->enableLogging();
    }
}
