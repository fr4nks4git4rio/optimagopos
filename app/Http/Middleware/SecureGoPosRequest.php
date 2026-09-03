<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SecureGoPosRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $maxBytes = (int) config('services.gopos.max_body_bytes', 1048576);
        $contentLength = (int) $request->header('Content-Length', 0);

        if ($contentLength > $maxBytes || strlen($request->getContent()) > $maxBytes) {
            return response()->json(['success' => false, 'message' => 'Payload too large.'], 413);
        }

        if (!str_contains(strtolower((string) $request->header('Content-Type')), 'application/json')) {
            return response()->json(['success' => false, 'message' => 'JSON content required.'], 415);
        }

        $raw = $request->getContent();
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON.'], 400);
        }

        $request->attributes->set('gopos_payload', $payload);
        $request->attributes->set('gopos_terminal_id', $payload['TerminalId'] ?? $payload['MerchantFiscalId'] ?? $payload['APIUserName'] ?? null);

        if (!config('services.gopos.require_signature')) {
            return $next($request);
        }

        $secret = (string) config('services.gopos.signature_secret');
        $timestamp = (string) $request->header('X-GoPos-Timestamp');
        $signature = (string) $request->header('X-GoPos-Signature');

        if ($secret === '' || $timestamp === '' || $signature === '' || !ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > (int) config('services.gopos.signature_tolerance', 300)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $expected = hash_hmac('sha256', $timestamp . "\n" . $raw, $secret);
        if (!hash_equals($expected, $signature)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $replayKey = 'gopos:request:' . hash('sha256', $signature);
        if (!Cache::add($replayKey, true, (int) config('services.gopos.signature_tolerance', 300))) {
            return response()->json(['success' => false, 'message' => 'Request already processed.'], 409);
        }

        return $next($request);
    }
}
