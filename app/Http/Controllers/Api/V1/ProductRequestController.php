<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductRequestController extends Controller
{
    // POST /api/v1/product-requests
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'brand_name'   => 'nullable|string|max:255',
            'dosage_form'  => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $ulid = (string) Str::ulid();

        DB::table('product_requests')->insert([
            'ulid'         => $ulid,
            'facility_id'  => $request->user()->facility_id,
            'product_name' => $validated['product_name'],
            'brand_name'   => $validated['brand_name'] ?? null,
            'dosage_form'  => $validated['dosage_form'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'status'       => 'RECEIVED',
            'created_at'   => Carbon::now('UTC'),
            'updated_at'   => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message' => 'Product request received. You will be notified on outcome.',
            'ulid'    => $ulid,
        ], 201);
    }

    // GET /api/v1/product-requests
    public function index(Request $request): JsonResponse
    {
        $requests = DB::table('product_requests')
            ->where('facility_id', $request->user()->facility_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    // GET /api/v1/admin/product-requests
    public function adminIndex(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Aggregated view — grouped by product name
        $aggregated = DB::table('product_requests as pr')
            ->join('facilities as f', 'pr.facility_id', '=', 'f.id')
            ->select([
                'pr.product_name',
                'pr.brand_name',
                'pr.dosage_form',
                DB::raw('COUNT(DISTINCT pr.facility_id) as requesting_facilities'),
                DB::raw('MIN(pr.created_at) as first_request_date'),
                DB::raw('MAX(pr.created_at) as last_request_date'),
                DB::raw('GROUP_CONCAT(f.facility_name ORDER BY pr.created_at SEPARATOR ", ") as facility_names'),
            ])
            ->where('pr.status', 'RECEIVED')
            ->groupBy('pr.product_name', 'pr.brand_name', 'pr.dosage_form')
            ->orderByDesc(DB::raw('COUNT(DISTINCT pr.facility_id)'))
            ->paginate(30);

        return response()->json($aggregated);
    }

    // PATCH /api/v1/admin/product-requests/{ulid}/action
    public function action(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'action'             => 'required|in:ADDED,REJECTED',
            'review_notes'       => 'nullable|string|max:500',
            'matched_product_id' => 'nullable|integer|exists:products,id',
        ]);

        $updated = DB::table('product_requests')
            ->where('ulid', $ulid)
            ->update([
                'status'             => $validated['action'],
                'reviewed_by'        => $request->user()->id,
                'review_notes'       => $validated['review_notes'] ?? null,
                'matched_product_id' => $validated['matched_product_id'] ?? null,
                'updated_at'         => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Product request not found.'], 404);
        }

        return response()->json(['message' => 'Product request updated.']);
    }
}
