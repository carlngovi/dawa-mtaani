<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorisedPlacerController extends Controller
{
    // GET /api/v1/admin/facilities/{ulid}/authorised-placers
    public function index(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $facility = DB::table('facilities')->where('ulid', $ulid)->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        $placers = DB::table('facility_authorised_placers as fap')
            ->join('users as u', 'fap.user_id', '=', 'u.id')
            ->where('fap.facility_id', $facility->id)
            ->where('fap.is_active', true)
            ->select(['fap.id', 'u.id as user_id', 'u.name', 'u.email', 'u.phone', 'fap.added_at'])
            ->get();

        return response()->json(['placers' => $placers]);
    }

    // POST /api/v1/admin/facilities/{ulid}/authorised-placers
    public function store(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        // Check if already an authorised placer
        $exists = DB::table('facility_authorised_placers')
            ->where('facility_id', $facility->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This user is already an authorised placer for this facility.',
            ], 422);
        }

        $now = Carbon::now('UTC');

        DB::table('facility_authorised_placers')->insert([
            'facility_id' => $facility->id,
            'user_id'     => $validated['user_id'],
            'added_by'    => $request->user()->id,
            'added_at'    => $now,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return response()->json(['message' => 'Authorised placer added.'], 201);
    }

    // DELETE /api/v1/admin/facilities/{ulid}/authorised-placers/{userId}
    public function destroy(Request $request, string $ulid, int $userId): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $facility = DB::table('facilities')->where('ulid', $ulid)->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        $deleted = DB::table('facility_authorised_placers')
            ->where('facility_id', $facility->id)
            ->where('user_id', $userId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Authorised placer not found.'], 404);
        }

        return response()->json(['message' => 'Authorised placer removed.']);
    }
}
