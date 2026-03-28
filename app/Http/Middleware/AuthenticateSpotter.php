<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Http\Middleware;

use App\Services\SpotterTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSpotter
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Missing token'], 401);
        }

        $rawToken = substr($header, 7);
        $token = app(SpotterTokenService::class)->resolve($rawToken);

        if (! $token) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        $token->update(['last_used_at' => now()]);

        $request->merge(['spotter_token' => $token]);
        $request->setUserResolver(fn () => $token->spotter);

        return $next($request);
    }
}
