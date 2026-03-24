<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MpesaIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('security.ip_whitelist.mpesa_callbacks', []);

        if (! in_array($request->ip(), $allowedIps)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
