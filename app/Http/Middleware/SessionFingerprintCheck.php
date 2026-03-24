<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SessionFingerprintCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $currentFingerprint = $this->generateFingerprint($request);
        $sessionId = $request->session()->getId();

        $stored = DB::table('session_fingerprints')
            ->where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (! $stored) {
            return $next($request);
        }

        if (! hash_equals($stored->device_fingerprint, $currentFingerprint)) {
            $request->session()->invalidate();

            return response()->json([
                'message' => 'Session invalid. Please log in again.',
                'code' => 'SESSION_FINGERPRINT_MISMATCH',
            ], 401);
        }

        return $next($request);
    }

    private function generateFingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->userAgent() ?? '',
            $request->header('Accept-Language') ?? '',
        ]));
    }
}
