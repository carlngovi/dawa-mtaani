<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinancialSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $timeoutMinutes = config('security.session.financial_timeout_minutes', 15);
        $lastActivity = $request->session()->get('financial_last_activity');

        if ($lastActivity && (time() - $lastActivity) > ($timeoutMinutes * 60)) {
            $request->session()->forget('financial_last_activity');

            return response()->json([
                'message' => 'Your session has expired due to inactivity. Please re-authenticate.',
                'code' => 'FINANCIAL_SESSION_EXPIRED',
            ], 401);
        }

        $request->session()->put('financial_last_activity', time());

        return $next($request);
    }
}
