<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityEventController extends Controller
{
    // GET /api/v1/admin/security/events
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('security_events as se')
            ->leftJoin('users as u', 'se.user_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'se.facility_id', '=', 'f.id')
            ->select([
                'se.*',
                'u.name as user_name',
                'u.email as user_email',
                'f.facility_name',
            ])
            ->orderBy('se.created_at', 'desc');

        if ($request->filled('severity')) {
            $query->where('se.severity', $request->severity);
        }

        if ($request->filled('event_type')) {
            $query->where('se.event_type', $request->event_type);
        }

        if ($request->filled('resolved')) {
            $request->boolean('resolved')
                ? $query->whereNotNull('se.resolved_at')
                : $query->whereNull('se.resolved_at');
        }

        if ($request->filled('user_id')) {
            $query->where('se.user_id', $request->user_id);
        }

        return response()->json($query->paginate(50));
    }

    // PATCH /api/v1/admin/security/events/{id}/resolve
    public function resolve(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $updated = DB::table('security_events')
            ->where('id', $id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at'      => Carbon::now('UTC'),
                'resolved_by'      => $request->user()->id,
                'resolution_notes' => $validated['resolution_notes'],
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Event not found or already resolved.'], 404);
        }

        return response()->json(['message' => 'Security event resolved.']);
    }

    // GET /api/v1/admin/security/summary
    public function summary(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $since = Carbon::now('UTC')->subHours(24);

        $bySeverity = DB::table('security_events')
            ->where('created_at', '>=', $since)
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        $unresolved = DB::table('security_events')
            ->whereNull('resolved_at')
            ->whereIn('severity', ['HIGH', 'CRITICAL'])
            ->count();

        return response()->json([
            'last_24h_by_severity' => $bySeverity,
            'unresolved_high_critical' => $unresolved,
            'generated_at' => Carbon::now('UTC')->toISOString(),
        ]);
    }
}
