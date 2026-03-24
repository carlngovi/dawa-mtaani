<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\DeletionProcessingJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeletionRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_method' => 'required|in:PLATFORM,WRITTEN',
        ]);

        $facilityId = $request->user()->facility_id;

        if (! $facilityId) {
            return response()->json(['message' => 'No facility associated with your account.'], 422);
        }

        $pending = DB::table('data_deletion_requests')
            ->where('facility_id', $facilityId)
            ->whereIn('status', ['PENDING', 'APPROVED', 'PROCESSING'])
            ->exists();

        if ($pending) {
            return response()->json([
                'message' => 'A deletion request is already in progress for this facility.',
            ], 422);
        }

        $ulid = Str::ulid();
        $slaDeadline = Carbon::now('UTC')->addDays(30);

        $id = DB::table('data_deletion_requests')->insertGetId([
            'ulid'            => $ulid,
            'facility_id'     => $facilityId,
            'requested_by'    => $request->user()->id,
            'request_method'  => $validated['request_method'],
            'status'          => 'PENDING',
            'sla_deadline_at' => $slaDeadline,
            'created_at'      => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message'      => 'Deletion request submitted. You will be notified on completion.',
            'ulid'         => $ulid,
            'sla_deadline' => $slaDeadline->toISOString(),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $requests = DB::table('data_deletion_requests')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($requests);
    }

    public function approve(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $deletionRequest = DB::table('data_deletion_requests')
            ->where('ulid', $ulid)
            ->where('status', 'PENDING')
            ->first();

        if (! $deletionRequest) {
            return response()->json(['message' => 'Request not found or not pending.'], 404);
        }

        DB::table('data_deletion_requests')
            ->where('ulid', $ulid)
            ->update([
                'status'      => 'APPROVED',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => Carbon::now('UTC'),
            ]);

        DeletionProcessingJob::dispatch($deletionRequest->id)
            ->onQueue('default');

        return response()->json(['message' => 'Request approved. Processing queued.']);
    }

    public function reject(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $updated = DB::table('data_deletion_requests')
            ->where('ulid', $ulid)
            ->where('status', 'PENDING')
            ->update([
                'status'            => 'REJECTED',
                'reviewed_by'       => $request->user()->id,
                'reviewed_at'       => Carbon::now('UTC'),
                'rejection_reason'  => $validated['reason'],
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Request not found or not pending.'], 404);
        }

        return response()->json(['message' => 'Request rejected.']);
    }
}
