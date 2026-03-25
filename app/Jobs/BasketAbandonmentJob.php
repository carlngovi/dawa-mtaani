<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BasketAbandonmentJob extends MonitoredJob
{
    public $queue = 'default';

    protected function execute(): void
    {
        $expiredBaskets = DB::table('patient_baskets')
            ->where('reserved_until', '<', now())
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('patient_orders')
                    ->whereColumn('patient_orders.facility_id', 'patient_baskets.facility_id')
                    ->whereColumn('patient_orders.patient_phone', 'patient_baskets.patient_phone');
            })
            ->get();

        $processed = 0;

        foreach ($expiredBaskets as $basket) {
            $lines = DB::table('patient_basket_lines')
                ->where('basket_id', $basket->id)
                ->get();

            foreach ($lines as $line) {
                DB::table('basket_abandonment_log')->insert([
                    'patient_phone' => $basket->patient_phone,
                    'facility_id'   => $basket->facility_id,
                    'product_id'    => $line->product_id,
                    'quantity'      => $line->quantity,
                    'abandoned_at'  => now(),
                ]);
            }

            DB::table('patient_basket_lines')->where('basket_id', $basket->id)->delete();
            DB::table('patient_baskets')->where('id', $basket->id)->delete();

            $processed++;
        }

        Log::info('BasketAbandonmentJob completed', ['processed' => $processed]);

        $this->completed($processed);
    }
}
