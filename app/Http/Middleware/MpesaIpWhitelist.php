<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MpesaIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        // In sandbox mode we skip IP enforcement — Safaricom's sandbox
        // callbacks come from unpredictable IPs. In production every
        // callback must originate from a whitelisted Safaricom IP.
        if (config('daraja.env') === 'sandbox') {
            return $next($request);
        }

        $allowedIps = config('daraja.callback_ips', []);

        if (! in_array($request->ip(), $allowedIps)) {
            \Illuminate\Support\Facades\Log::warning('MpesaIpWhitelist: blocked callback from unexpected IP', [
                'ip'  => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
