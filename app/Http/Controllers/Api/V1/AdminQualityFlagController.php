<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BatchAlertService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminQualityFlagController extends Controller
{
    public function __construct(
        private readonly BatchAlertService $batchAlertService
    ) {}

    // GET /api/v1/admin/quality-flags
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('quality_flags as qf')
            ->join('products as p', 'qf.product_id', '=', 'p.id')
            ->join('facilities as f', 'qf.facility_id', '=', 'f.id')
            ->select([
                'qf.*',
                'p.generic_name',
                'p.sku_code',
                'p.brand_name',
                'f.facility_name',
                'f.county',
                // facility_id exposed to admin only
            ])
            ->orderBy('qf.created_at', 'desc');

        if ($request->filled('status'))     $query->where('qf.status', $request->status);
        if ($request->filled('flag_type'))  $query->where('qf.flag_type', $request->flag_type);
        if ($request->filled('product_id')) $query->where('qf.product_id', $request->product_id);
        if ($request->filled('county'))     $query->where('f.county', $request->county);

        return response()->json($query->paginate(30));
    }

    // PATCH /api/v1/admin/quality-flags/{ulid}/review
    public function review(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $updated = DB::table('quality_flags')
            ->where('ulid', $ulid)
            ->update([
                'status'       => 'UNDER_REVIEW',
                'reviewed_by'  => $request->user()->id,
                'review_notes' => $validated['review_notes'] ?? null,
                'updated_at'   => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Flag not found.'], 404);
        }

        return response()->json(['message' => 'Flag marked as under review.']);
    }

    // POST /api/v1/admin/quality-flags/{ulid}/confirm
    public function confirm(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $flag = DB::table('quality_flags')
            ->where('ulid', $ulid)
            ->first();

        if (! $flag) {
            return response()->json(['message' => 'Flag not found.'], 404);
        }

        $now = Carbon::now('UTC');

        DB::transaction(function () use ($flag, $request, $now) {
            DB::table('quality_flags')
                ->where('id', $flag->id)
                ->update([
                    'status'      => 'CONFIRMED',
                    'reviewed_by' => $request->user()->id,
                    'updated_at'  => $now,
                ]);

            // Dispatch batch alerts to all facilities that received the product
            $this->batchAlertService->dispatch($flag->id);
        });

        $updatedFlag = DB::table('quality_flags')->where('id', $flag->id)->first();

        return response()->json([
            'message'                  => 'Flag confirmed. Batch alerts dispatched.',
            'batch_alert_facility_count' => $updatedFlag->batch_alert_facility_count,
        ]);
    }

    // POST /api/v1/admin/quality-flags/{ulid}/dismiss
    public function dismiss(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $updated = DB::table('quality_flags')
            ->where('ulid', $ulid)
            ->update([
                'status'       => 'DISMISSED',
                'reviewed_by'  => $request->user()->id,
                'review_notes' => $validated['review_notes'] ?? null,
                'updated_at'   => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Flag not found.'], 404);
        }

        return response()->json(['message' => 'Flag dismissed.']);
    }
}
