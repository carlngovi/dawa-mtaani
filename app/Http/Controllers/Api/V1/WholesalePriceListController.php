<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WholesalePriceListController extends Controller
{
    // GET /api/v1/wholesale/price-lists
    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $priceLists = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->where('wpl.wholesale_facility_id', $facilityId)
            ->select([
                'wpl.*',
                'p.generic_name',
                'p.brand_name',
                'p.sku_code',
                'p.therapeutic_category',
            ])
            ->orderBy('p.generic_name')
            ->paginate(50);

        $currency = CurrencyConfig::get();

        return response()->json([
            'price_lists' => $priceLists,
            'currency'    => $currency['symbol'],
        ]);
    }

    // POST /api/v1/wholesale/price-lists
    public function store(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $validated = $request->validate([
            'product_id'     => 'required|integer|exists:products,id',
            'unit_price'     => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'expires_at'     => 'nullable|date|after:effective_from',
            'stock_status'   => 'required|in:IN_STOCK,LOW_STOCK,OUT_OF_STOCK',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        $now = Carbon::now('UTC');

        $id = DB::table('wholesale_price_lists')->insertGetId([
            'wholesale_facility_id' => $facilityId,
            'product_id'            => $validated['product_id'],
            'unit_price'            => $validated['unit_price'],
            'effective_from'        => $validated['effective_from'],
            'expires_at'            => $validated['expires_at'] ?? null,
            'stock_status'          => $validated['stock_status'],
            'stock_quantity'        => $validated['stock_quantity'] ?? null,
            'is_active'             => true,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        return response()->json([
            'message' => 'Price list entry created.',
            'id'      => $id,
        ], 201);
    }

    // PATCH /api/v1/wholesale/price-lists/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $priceList = DB::table('wholesale_price_lists')
            ->where('id', $id)
            ->where('wholesale_facility_id', $facilityId)
            ->first();

        if (! $priceList) {
            return response()->json(['message' => 'Price list entry not found.'], 404);
        }

        $validated = $request->validate([
            'unit_price'     => 'sometimes|numeric|min:0',
            'effective_from' => 'sometimes|date',
            'expires_at'     => 'nullable|date',
            'stock_status'   => 'sometimes|in:IN_STOCK,LOW_STOCK,OUT_OF_STOCK',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        $validated['updated_at'] = Carbon::now('UTC');

        DB::table('wholesale_price_lists')
            ->where('id', $id)
            ->update($validated);

        return response()->json(['message' => 'Price list entry updated.']);
    }

    // DELETE /api/v1/wholesale/price-lists/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $deleted = DB::table('wholesale_price_lists')
            ->where('id', $id)
            ->where('wholesale_facility_id', $facilityId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Price list entry not found.'], 404);
        }

        return response()->json(['message' => 'Price list entry deleted.']);
    }
}
