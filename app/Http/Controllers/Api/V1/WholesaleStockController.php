<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WholesaleStockController extends Controller
{
    // POST /api/v1/wholesale/stock-status
    public function bulkUpdate(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $validated = $request->validate([
            'updates'                  => 'required|array|min:1',
            'updates.*.product_id'     => 'required|integer|exists:products,id',
            'updates.*.stock_status'   => 'required|in:IN_STOCK,LOW_STOCK,OUT_OF_STOCK',
            'updates.*.stock_quantity' => 'nullable|integer|min:0',
        ]);

        $processed = 0;
        $now = Carbon::now('UTC');

        foreach ($validated['updates'] as $update) {
            $existing = DB::table('facility_stock_status')
                ->where('wholesale_facility_id', $facilityId)
                ->where('product_id', $update['product_id'])
                ->first();

            if ($existing) {
                DB::table('facility_stock_status')
                    ->where('id', $existing->id)
                    ->update([
                        'stock_status'   => $update['stock_status'],
                        'stock_quantity' => $update['stock_quantity'] ?? null,
                        'updated_by'     => $request->user()->id,
                        'updated_at'     => $now,
                    ]);
            } else {
                DB::table('facility_stock_status')->insert([
                    'wholesale_facility_id' => $facilityId,
                    'product_id'            => $update['product_id'],
                    'stock_status'          => $update['stock_status'],
                    'stock_quantity'        => $update['stock_quantity'] ?? null,
                    'updated_by'            => $request->user()->id,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }

            // Also sync to wholesale_price_lists stock_status
            DB::table('wholesale_price_lists')
                ->where('wholesale_facility_id', $facilityId)
                ->where('product_id', $update['product_id'])
                ->where('is_active', true)
                ->update([
                    'stock_status'   => $update['stock_status'],
                    'stock_quantity' => $update['stock_quantity'] ?? null,
                    'updated_at'     => $now,
                ]);

            $processed++;
        }

        return response()->json([
            'message'   => 'Stock status updated.',
            'processed' => $processed,
        ]);
    }

    // GET /api/v1/wholesale/performance
    public function performance(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;
        $currency = CurrencyConfig::get();

        // Fulfilment rate — confirmed vs total orders
        $totalOrders = DB::table('orders as o')
            ->whereExists(function ($q) use ($facilityId) {
                $q->select(DB::raw(1))
                  ->from('order_lines')
                  ->whereColumn('order_lines.order_id', 'o.id')
                  ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->whereNull('o.deleted_at')
            ->count();

        $deliveredOrders = DB::table('orders as o')
            ->whereExists(function ($q) use ($facilityId) {
                $q->select(DB::raw(1))
                  ->from('order_lines')
                  ->whereColumn('order_lines.order_id', 'o.id')
                  ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->whereIn('o.status', ['DELIVERED'])
            ->whereNull('o.deleted_at')
            ->count();

        $fulfilmentRate = $totalOrders > 0
            ? round(($deliveredOrders / $totalOrders) * 100, 2)
            : 0;

        // Average dispatch time
        $avgDispatchTime = DB::table('dispatch_triggers')
            ->where('triggered_by_facility_id', $facilityId)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, triggered_at)'));

        // Price list freshness
        $activePriceLists = DB::table('wholesale_price_lists')
            ->where('wholesale_facility_id', $facilityId)
            ->where('is_active', true)
            ->count();

        $expiredPriceLists = DB::table('wholesale_price_lists')
            ->where('wholesale_facility_id', $facilityId)
            ->where('is_active', false)
            ->where('expires_at', '>=', now()->subDays(30)->toDateString())
            ->count();

        return response()->json([
            'fulfilment_rate_pct'       => $fulfilmentRate,
            'total_orders'              => $totalOrders,
            'delivered_orders'          => $deliveredOrders,
            'avg_dispatch_time_hours'   => round($avgDispatchTime ?? 0, 1),
            'active_price_lists'        => $activePriceLists,
            'recently_expired_lists'    => $expiredPriceLists,
            'currency'                  => $currency['symbol'],
            'generated_at'              => now('UTC')->toISOString(),
        ]);
    }
}
