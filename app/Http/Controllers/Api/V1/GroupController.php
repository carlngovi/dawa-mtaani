<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    // POST /api/v1/admin/groups
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'group_name'         => 'required|string|max:255',
            'group_owner_name'   => 'required|string|max:255',
            'group_owner_phone'  => ['required', 'string', 'regex:/^(\+254|07|01)\d{8,9}$/'],
            'group_owner_email'  => 'nullable|email|max:255',
        ]);

        $ulid = (string) Str::ulid();
        $now = Carbon::now('UTC');

        $id = DB::table('pharmacy_groups')->insertGetId([
            'ulid'                => $ulid,
            'group_name'          => $validated['group_name'],
            'group_owner_name'    => $validated['group_owner_name'],
            'group_owner_phone'   => $validated['group_owner_phone'],
            'group_owner_email'   => $validated['group_owner_email'] ?? null,
            'is_active'           => true,
            'created_by'          => $request->user()->id,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        return response()->json([
            'message'    => 'Group created.',
            'ulid'       => $ulid,
            'group_name' => $validated['group_name'],
        ], 201);
    }

    // POST /api/v1/admin/groups/{groupUlid}/members
    public function addMember(Request $request, string $groupUlid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'facility_ulid' => 'required|string',
        ]);

        $group = DB::table('pharmacy_groups')
            ->where('ulid', $groupUlid)
            ->first();

        if (! $group) {
            return response()->json(['message' => 'Group not found.'], 404);
        }

        $facility = DB::table('facilities')
            ->where('ulid', $validated['facility_ulid'])
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        // Check facility is not already in a group
        $existing = DB::table('pharmacy_group_members')
            ->where('facility_id', $facility->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'This facility is already a member of a group.',
            ], 422);
        }

        $now = Carbon::now('UTC');

        DB::table('pharmacy_group_members')->insert([
            'group_id'    => $group->id,
            'facility_id' => $facility->id,
            'added_by'    => $request->user()->id,
            'added_at'    => $now,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return response()->json(['message' => 'Facility added to group.'], 201);
    }

    // DELETE /api/v1/admin/groups/{groupUlid}/members/{facilityUlid}
    public function removeMember(Request $request, string $groupUlid, string $facilityUlid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $group = DB::table('pharmacy_groups')->where('ulid', $groupUlid)->first();
        $facility = DB::table('facilities')->where('ulid', $facilityUlid)->first();

        if (! $group || ! $facility) {
            return response()->json(['message' => 'Group or facility not found.'], 404);
        }

        $deleted = DB::table('pharmacy_group_members')
            ->where('group_id', $group->id)
            ->where('facility_id', $facility->id)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Facility is not a member of this group.'], 404);
        }

        return response()->json(['message' => 'Facility removed from group.']);
    }
}
