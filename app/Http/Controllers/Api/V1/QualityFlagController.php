<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QualityFlagController extends Controller
{
    // POST /api/v1/quality-flags
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'      => 'required|integer|exists:products,id',
            'batch_reference' => 'nullable|string|max:100',
            'flag_type'       => 'required|in:SUSPECTED_COUNTERFEIT,PACKAGING_ANOMALY,LABELLING_CONCERN,QUALITY_DEGRADATION,OTHER',
            'notes'           => 'nullable|string|max:2000',
            'photo_path'      => 'nullable|string|max:500',
        ]);

        $ulid = (string) Str::ulid();
        $now = Carbon::now('UTC');

        DB::table('quality_flags')->insert([
            'ulid'            => $ulid,
            'facility_id'     => $request->user()->facility_id,
            'product_id'      => $validated['product_id'],
            'batch_reference' => $validated['batch_reference'] ?? null,
            'flag_type'       => $validated['flag_type'],
            'notes'           => $validated['notes'] ?? null,
            'photo_path'      => $validated['photo_path'] ?? null,
            'status'          => 'OPEN',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // Log to audit trail — internal record with facility identity
        DB::table('audit_logs')->insert([
            'facility_id' => $request->user()->facility_id,
            'user_id'     => $request->user()->id,
            'action'      => 'quality_flag_submitted',
            'model_type'  => 'QualityFlag',
            'payload_after' => json_encode([
                'ulid'       => $ulid,
                'flag_type'  => $validated['flag_type'],
                'product_id' => $validated['product_id'],
            ]),
            'ip_address'  => $request->ip() ?? '0.0.0.0',
            'created_at'  => $now,
        ]);

        // Record business metric
        DB::table('business_metric_snapshots')->insert([
            'metric_name'  => 'quality_flags_submitted',
            'metric_value' => 1,
            'window_start' => $now->copy()->floorMinutes(15),
            'window_end'   => $now->copy()->floorMinutes(15)->addMinutes(15),
            'created_at'   => $now,
        ]);

        // Return acknowledgement only — no facility identity
        return response()->json([
            'status'  => 'success',
            'message' => 'Report received. Thank you.',
        ], 201);
    }

    // GET /api/v1/quality-flags/my-flags
    public function myFlags(Request $request): JsonResponse
    {
        $flags = DB::table('quality_flags as qf')
            ->join('products as p', 'qf.product_id', '=', 'p.id')
            ->where('qf.facility_id', $request->user()->facility_id)
            ->select([
                'qf.ulid',
                'qf.flag_type',
                'qf.status',
                'qf.created_at',
                'p.generic_name',
                'p.sku_code',
                // NOTE: facility_id NOT returned — anonymisation
                // NOTE: review_notes NOT returned — no investigation detail
            ])
            ->orderBy('qf.created_at', 'desc')
            ->paginate(20);

        return response()->json($flags);
    }
}
