<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityFlagController extends Controller
{
    public function store(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|in:LATE_PAYMENT,LOW_ORDER_FREQUENCY,DISPUTE_PATTERN,OTHER',
            'notes'  => 'nullable|string|max:500',
        ]);

        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        $now = Carbon::now('UTC');

        $id = DB::table('facility_flags')->insertGetId([
            'facility_id' => $facility->id,
            'flagged_by'  => $request->user()->id,
            'reason'      => $validated['reason'],
            'notes'       => $validated['notes'] ?? null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return response()->json([
            'message' => 'Facility flagged for follow-up.',
            'flag_id' => $id,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('facility_flags as ff')
            ->join('facilities as f', 'ff.facility_id', '=', 'f.id')
            ->join('users as u', 'ff.flagged_by', '=', 'u.id')
            ->select([
                'ff.*',
                'f.facility_name',
                'f.county',
                'f.network_membership',
                'u.name as flagged_by_name',
            ])
            ->orderBy('ff.created_at', 'desc');

        if ($request->filled('reason')) {
            $query->where('ff.reason', $request->reason);
        }

        if ($request->filled('resolved')) {
            $request->boolean('resolved')
                ? $query->whereNotNull('ff.resolved_at')
                : $query->whereNull('ff.resolved_at');
        }

        return response()->json($query->paginate(30));
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $updated = DB::table('facility_flags')
            ->where('id', $id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => Carbon::now('UTC'),
                'resolved_by' => $request->user()->id,
                'updated_at'  => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Flag not found or already resolved.'], 404);
        }

        return response()->json(['message' => 'Flag resolved.']);
    }
}
