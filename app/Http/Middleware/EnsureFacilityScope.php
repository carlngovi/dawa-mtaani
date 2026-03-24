<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureFacilityScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Network admin and system roles bypass facility scope check
        if ($user->hasRole(['network_admin', 'system'])) {
            return $next($request);
        }

        // Get facility identifier from route — supports both {ulid} and {facility}
        $routeFacilityUlid = $request->route('ulid')
            ?? $request->route('facility')
            ?? $request->route('facilityUlid');

        // If no facility in route, pass through — controller handles scoping
        if (! $routeFacilityUlid) {
            return $next($request);
        }

        // Verify the authenticated user belongs to the requested facility
        $userFacility = DB::table('facilities')
            ->where('ulid', $routeFacilityUlid)
            ->value('id');

        if ($userFacility && $user->facility_id !== $userFacility) {
            return response()->json([
                'message' => 'You do not have access to this facility.',
            ], 403);
        }

        return $next($request);
    }
}
