<?php

namespace App\Observers;

use App\Services\BusinessMetricCollector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacilityStockStatusObserver
{
    public function updated($stockStatus): void
    {
        // Invalidate store search cache on any stock change
        try {
            Cache::tags(['store_search'])->flush();
        } catch (\Throwable $e) {
            Log::warning('FacilityStockStatusObserver: failed to flush store_search cache', [
                'error' => $e->getMessage(),
            ]);
        }

        $oldStatus = $stockStatus->getOriginal('stock_status');
        $newStatus = $stockStatus->stock_status;

        if ($oldStatus === $newStatus) {
            return;
        }

        if ($newStatus === 'OUT_OF_STOCK') {
            $this->handleOutOfStock($stockStatus);
        }

        if ($newStatus === 'IN_STOCK') {
            $this->handleInStock($stockStatus);
        }
    }

    private function handleOutOfStock($stockStatus): void
    {
        try {
            // Deactivate price list for this wholesale facility + product
            DB::table('wholesale_price_lists')
                ->where('wholesale_facility_id', $stockStatus->wholesale_facility_id)
                ->where('product_id', $stockStatus->product_id)
                ->where('is_active', true)
                ->update([
                    'stock_status' => 'OUT_OF_STOCK',
                    'updated_at'   => Carbon::now('UTC'),
                ]);

            Log::info('FacilityStockStatusObserver: product removed from catalogue', [
                'wholesale_facility_id' => $stockStatus->wholesale_facility_id,
                'product_id'            => $stockStatus->product_id,
            ]);

        } catch (\Throwable $e) {
            Log::error('FacilityStockStatusObserver: failed to handle OUT_OF_STOCK', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleInStock($stockStatus): void
    {
        try {
            // Re-activate price list if active price exists
            $priceList = DB::table('wholesale_price_lists')
                ->where('wholesale_facility_id', $stockStatus->wholesale_facility_id)
                ->where('product_id', $stockStatus->product_id)
                ->where('effective_from', '<=', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now()->toDateString());
                })
                ->first();

            if ($priceList) {
                DB::table('wholesale_price_lists')
                    ->where('id', $priceList->id)
                    ->update([
                        'stock_status' => 'IN_STOCK',
                        'is_active'    => true,
                        'updated_at'   => Carbon::now('UTC'),
                    ]);
            }

            // Fire restock notifications to subscribed facilities
            $subscriptions = DB::table('facility_restock_subscriptions')
                ->where('product_id', $stockStatus->product_id)
                ->where(function ($q) use ($stockStatus) {
                    $q->whereNull('wholesale_facility_id')
                      ->orWhere('wholesale_facility_id', $stockStatus->wholesale_facility_id);
                })
                ->get();

            foreach ($subscriptions as $subscription) {
                try {
                    DB::table('facility_restock_subscriptions')
                        ->where('id', $subscription->id)
                        ->update(['notified_at' => Carbon::now('UTC')]);

                    // Record metric
                    BusinessMetricCollector::record('restock_notifications_sent', 1);

                    Log::info('FacilityStockStatusObserver: restock notification sent', [
                        'facility_id' => $subscription->facility_id,
                        'product_id'  => $stockStatus->product_id,
                    ]);

                } catch (\Throwable $e) {
                    Log::error('FacilityStockStatusObserver: failed to notify facility', [
                        'facility_id' => $subscription->facility_id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('FacilityStockStatusObserver: failed to handle IN_STOCK', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
