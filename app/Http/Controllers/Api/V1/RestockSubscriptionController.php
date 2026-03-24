<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestockSubscriptionController extends Controller
{
    // POST /api/v1/restock-subscriptions/{productId}
    public function store(Request $request, int $productId): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $validated = $request->validate([
            'wholesale_facility_id' => 'nullable|integer|exists:facilities,id',
        ]);

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $wholesaleFacilityId = $validated['wholesale_facility_id'] ?? null;

        $exists = DB::table('facility_restock_subscriptions')
            ->where('facility_id', $facilityId)
            ->where('product_id', $productId)
            ->where('wholesale_facility_id', $wholesaleFacilityId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Already subscribed to restock alerts for this product.',
            ], 422);
        }

        DB::table('facility_restock_subscriptions')->insert([
            'facility_id'           => $facilityId,
            'product_id'            => $productId,
            'wholesale_facility_id' => $wholesaleFacilityId,
            'subscribed_at'         => now('UTC'),
            'created_at'            => now('UTC'),
            'updated_at'            => now('UTC'),
        ]);

        return response()->json(['message' => 'Subscribed to restock alerts.'], 201);
    }

    // DELETE /api/v1/restock-subscriptions/{productId}
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $deleted = DB::table('facility_restock_subscriptions')
            ->where('facility_id', $request->user()->facility_id)
            ->where('product_id', $productId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Subscription not found.'], 404);
        }

        return response()->json(['message' => 'Unsubscribed from restock alerts.']);
    }
}
