<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\DataExportJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataExportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        if (! $facilityId) {
            return response()->json(['message' => 'No facility associated with your account.'], 422);
        }

        $ulid = Str::ulid();

        $id = DB::table('data_export_requests')->insertGetId([
            'ulid'         => $ulid,
            'facility_id'  => $facilityId,
            'requested_by' => $request->user()->id,
            'status'       => 'PENDING',
            'created_at'   => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message' => 'Export request submitted. You will receive a download link when ready.',
            'ulid'    => $ulid,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $requests = DB::table('data_export_requests')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($requests);
    }

    public function approve(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $exportRequest = DB::table('data_export_requests')
            ->where('ulid', $ulid)
            ->where('status', 'PENDING')
            ->first();

        if (! $exportRequest) {
            return response()->json(['message' => 'Request not found or not pending.'], 404);
        }

        DB::table('data_export_requests')
            ->where('ulid', $ulid)
            ->update([
                'status'      => 'APPROVED',
                'approved_by' => $request->user()->id,
                'approved_at' => Carbon::now('UTC'),
            ]);

        DataExportJob::dispatch($exportRequest->id)
            ->onQueue('reports');

        return response()->json(['message' => 'Export approved. Generating archive.']);
    }

    public function download(Request $request, string $ulid): JsonResponse
    {
        $exportRequest = DB::table('data_export_requests')
            ->where('ulid', $ulid)
            ->where('status', 'READY')
            ->first();

        if (! $exportRequest) {
            return response()->json(['message' => 'Export not found or not ready.'], 404);
        }

        if ($exportRequest->download_expires_at < now()) {
            DB::table('data_export_requests')
                ->where('ulid', $ulid)
                ->update(['status' => 'EXPIRED']);

            return response()->json(['message' => 'Export link has expired. Please request a new export.'], 410);
        }

        DB::table('data_export_requests')
            ->where('ulid', $ulid)
            ->update(['downloaded_at' => Carbon::now('UTC')]);

        return response()->json([
            'download_url'       => $exportRequest->download_url,
            'expires_at'         => $exportRequest->download_expires_at,
        ]);
    }
}
