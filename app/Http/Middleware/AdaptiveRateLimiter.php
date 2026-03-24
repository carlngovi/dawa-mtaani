<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdaptiveRateLimiter
{
    public function handle(Request $request, Closure $next, string $tier = 'authenticated'): Response
    {
        [$key, $maxAttempts, $decaySeconds] = match ($tier) {
            'public' => [$request->ip(), config('security.rate_limits.public_per_minute', 60), 60],
            'sensitive' => ['sensitive:' . $request->user()?->id, config('security.rate_limits.sensitive_per_hour', 10), 3600],
            default => ['auth:' . $request->user()?->id, config('security.rate_limits.authenticated_per_minute', 300), 60],
        };

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after_seconds' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
