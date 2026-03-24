<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceListExpiryJob extends MonitoredJob
{
    public function execute(): void
    {
        $today = Carbon::now('UTC')->toDateString();
        $processed = 0;

        $expiredLists = DB::table('wholesale_price_lists')
            ->where('expires_at', '<=', $today)
            ->where('is_active', true)
            ->get();

        foreach ($expiredLists as $priceList) {
            try {
                // Deactivate the expired price list
                DB::table('wholesale_price_lists')
                    ->where('id', $priceList->id)
                    ->update([
                        'is_active'  => false,
                        'updated_at' => Carbon::now('UTC'),
                    ]);

                // Check if a next active price exists for this product
                $nextActivePrice = DB::table('wholesale_price_lists')
                    ->where('wholesale_facility_id', $priceList->wholesale_facility_id)
                    ->where('product_id', $priceList->product_id)
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', $today);
                    })
                    ->first();

                // If no next active price — remove from catalogue
                if (! $nextActivePrice) {
                    DB::table('facility_stock_status')
                        ->where('wholesale_facility_id', $priceList->wholesale_facility_id)
                        ->where('product_id', $priceList->product_id)
                        ->update([
                            'stock_status' => 'OUT_OF_STOCK',
                            'updated_at'   => Carbon::now('UTC'),
                        ]);

                    Log::info('PriceListExpiryJob: product removed from catalogue — no next price', [
                        'wholesale_facility_id' => $priceList->wholesale_facility_id,
                        'product_id'            => $priceList->product_id,
                    ]);
                }

                // Log to audit trail
                DB::table('audit_logs')->insert([
                    'action'        => 'price_list_expired',
                    'model_type'    => 'WholesalePriceList',
                    'model_id'      => $priceList->id,
                    'payload_after' => json_encode([
                        'expired_at'  => $today,
                        'has_next'    => (bool) $nextActivePrice,
                    ]),
                    'ip_address'    => '0.0.0.0',
                    'created_at'    => Carbon::now('UTC'),
                ]);

                $processed++;

            } catch (\Throwable $e) {
                Log::error('PriceListExpiryJob: failed for price list', [
                    'price_list_id' => $priceList->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        $this->completed($processed);
    }
}
