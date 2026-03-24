<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchAlertService
{
    public function dispatch(int $flagId): int
    {
        $flag = DB::table('quality_flags')
            ->where('id', $flagId)
            ->first();

        if (! $flag) {
            Log::error('BatchAlertService: flag not found', ['flag_id' => $flagId]);
            return 0;
        }

        $now = Carbon::now('UTC');

        // Find all facilities that received this product
        $query = DB::table('order_lines as ol')
            ->join('orders as o', 'ol.order_id', '=', 'o.id')
            ->join('delivery_confirmations as dc', 'dc.order_id', '=', 'o.id')
            ->where('ol.product_id', $flag->product_id)
            ->whereIn('dc.confirmation_type', ['RETAIL_CONFIRMED', 'AUTO_TRIGGERED'])
            ->whereNull('o.deleted_at')
            // Exclude originating facility
            ->where('o.retail_facility_id', '!=', $flag->facility_id);

        // If batch_reference provided — additional context for manual scope
        if ($flag->batch_reference) {
            Log::info('BatchAlertService: batch reference provided', [
                'flag_id'         => $flagId,
                'batch_reference' => $flag->batch_reference,
            ]);
        }

        $recipientFacilityIds = $query
            ->distinct()
            ->pluck('o.retail_facility_id');

        if ($recipientFacilityIds->isEmpty()) {
            Log::info('BatchAlertService: no recipient facilities found', [
                'flag_id'    => $flagId,
                'product_id' => $flag->product_id,
            ]);

            DB::table('quality_flags')
                ->where('id', $flagId)
                ->update([
                    'batch_alert_sent_at'         => $now,
                    'batch_alert_facility_count'  => 0,
                    'updated_at'                  => $now,
                ]);

            return 0;
        }

        $alertedCount = 0;

        foreach ($recipientFacilityIds as $facilityId) {
            try {
                DB::table('quality_flag_batch_alerts')->insert([
                    'quality_flag_id'    => $flagId,
                    'alerted_facility_id' => $facilityId,
                    'alerted_at'         => $now,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);

                // Log notification — text never mentions originating pharmacy
                DB::table('audit_logs')->insert([
                    'facility_id' => $facilityId,
                    'action'      => 'batch_quality_alert_sent',
                    'model_type'  => 'QualityFlag',
                    'model_id'    => $flagId,
                    'payload_after' => json_encode([
                        'product_id'      => $flag->product_id,
                        'batch_reference' => $flag->batch_reference,
                        'flag_type'       => $flag->flag_type,
                        // NOTE: originating facility NEVER included here
                    ]),
                    'ip_address'  => '0.0.0.0',
                    'created_at'  => $now,
                ]);

                $alertedCount++;

            } catch (\Throwable $e) {
                Log::error('BatchAlertService: failed to alert facility', [
                    'facility_id' => $facilityId,
                    'flag_id'     => $flagId,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        // Update flag with alert count
        DB::table('quality_flags')
            ->where('id', $flagId)
            ->update([
                'batch_alert_sent_at'        => $now,
                'batch_alert_facility_count' => $alertedCount,
                'updated_at'                 => $now,
            ]);

        Log::info('BatchAlertService: batch alerts dispatched', [
            'flag_id'  => $flagId,
            'alerted'  => $alertedCount,
        ]);

        return $alertedCount;
    }
}
