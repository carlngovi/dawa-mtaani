<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SavedCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavedCartController extends Controller
{
    public function __construct(
        private readonly SavedCartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $carts = DB::table('saved_carts')
            ->where(function ($q) use ($facilityId) {
                $q->where('owner_facility_id', $facilityId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['carts' => $carts]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.product_id'            => 'required|integer|exists:products,id',
            'lines.*.wholesale_facility_id' => 'required|integer|exists:facilities,id',
            'lines.*.quantity'              => 'required|integer|min:1',
        ]);

        $ulid = $this->cartService->create(
            name: $validated['name'],
            createdBy: $request->user()->id,
            ownerFacilityId: $request->user()->facility_id,
        );

        $cart = DB::table('saved_carts')->where('ulid', $ulid)->first();

        foreach ($validated['lines'] as $line) {
            DB::table('saved_cart_lines')->insert([
                'saved_cart_id'         => $cart->id,
                'product_id'            => $line['product_id'],
                'wholesale_facility_id' => $line['wholesale_facility_id'],
                'quantity'              => $line['quantity'],
                'created_at'            => now('UTC'),
                'updated_at'            => now('UTC'),
            ]);
        }

        return response()->json([
            'message' => 'Cart saved.',
            'ulid'    => $ulid,
        ], 201);
    }

    public function load(Request $request, string $ulid): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $result = $this->cartService->load($ulid, $facilityId);

        if (! $result['found']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json($result);
    }

    public function destroy(Request $request, string $ulid): JsonResponse
    {
        $deleted = DB::table('saved_carts')
            ->where('ulid', $ulid)
            ->where('owner_facility_id', $request->user()->facility_id)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        return response()->json(['message' => 'Cart deleted.']);
    }
}
