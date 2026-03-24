<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacCallback
{
    public function handle(Request $request, Closure $next, string $party = ''): Response
    {
        $secretKey = config("services.{$party}.webhook_secret");

        if (! $secretKey) {
            return response()->json(['message' => 'Webhook secret not configured.'], 500);
        }

        $signature = $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature-256');

        if (! $signature) {
            return response()->json(['message' => 'Missing callback signature.'], 401);
        }

        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $payload, $secretKey);

        if (! hash_equals($computed, $signature)) {
            return response()->json(['message' => 'Invalid callback signature.'], 401);
        }

        return $next($request);
    }
}
