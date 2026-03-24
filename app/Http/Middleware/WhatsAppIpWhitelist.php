<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedCidrs = config('security.ip_whitelist.whatsapp_webhook', []);
        $requestIp = $request->ip();

        foreach ($allowedCidrs as $cidr) {
            if ($this->ipInCidr($requestIp, $cidr)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = ~((1 << (32 - (int) $mask)) - 1);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
