<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavouriteController extends Controller
{
    // GET /api/v1/favourites
    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $favourites = DB::table('facility_favourite_products as ffp')
            ->join('products as p', 'ffp.product_id', '=', 'p.id')
            ->leftJoin('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.product_id', '=', 'p.id')
                     ->where('wpl.is_active', true)
                     ->where('wpl.stock_status', '!=', 'OUT_OF_STOCK');
            })
            ->where('ffp.facility_id', $facilityId)
            ->where('p.is_active', true)
            ->select([
                'p.id as product_id',
                'p.ulid as product_ulid',
                'p.sku_code',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'p.therapeutic_category',
                'wpl.unit_price',
                'wpl.stock_status',
                'wpl.wholesale_facility_id',
            ])
            ->orderBy('p.generic_name')
            ->get();

        return response()->json(['favourites' => $favourites]);
    }

    // POST /api/v1/favourites/{productId}
    public function store(Request $request, int $productId): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $product = DB::table('products')
            ->where('id', $productId)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $exists = DB::table('facility_favourite_products')
            ->where('facility_id', $facilityId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Product already in favourites.'], 422);
        }

        DB::table('facility_favourite_products')->insert([
            'facility_id' => $facilityId,
            'product_id'  => $productId,
            'added_by'    => $request->user()->id,
            'created_at'  => now('UTC'),
            'updated_at'  => now('UTC'),
        ]);

        return response()->json(['message' => 'Added to favourites.'], 201);
    }

    // DELETE /api/v1/favourites/{productId}
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $deleted = DB::table('facility_favourite_products')
            ->where('facility_id', $request->user()->facility_id)
            ->where('product_id', $productId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Product not in favourites.'], 404);
        }

        return response()->json(['message' => 'Removed from favourites.']);
    }
}
