<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacilitySettlementJob extends MonitoredJob
{
    public $queue = 'payments';

    protected function execute(): void
    {
        $yesterday = now()->subDay()->toDateString();

        $settlements = DB::table('customer_orders')
            ->where('status', 'COLLECTED')
            ->whereDate('collected_at', $yesterday)
            ->groupBy('facility_id')
            ->select([
                'facility_id',
                DB::raw('SUM(total_amount) as gross_amount'),
                DB::raw('SUM(platform_fee_amount) as platform_fee'),
                DB::raw('SUM(facility_net_amount) as net_amount'),
                DB::raw('COUNT(*) as order_count'),
            ])
            ->get();

        $processed = 0;

        foreach ($settlements as $row) {
            $facility = DB::table('facilities')->where('id', $row->facility_id)->first();

            DB::table('settlement_records')->insert([
                'facility_id'       => $row->facility_id,
                'settlement_date'   => $yesterday,
                'gross_amount'      => $row->gross_amount,
                'platform_fee'      => $row->platform_fee,
                'net_amount'        => $row->net_amount,
                'order_count'       => $row->order_count,
                'is_network_member' => $facility->network_membership === 'NETWORK',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // TODO: trigger MpesaService B2C bulk transfer here (to be wired in Module 21)

            $processed++;
        }

        Log::info('FacilitySettlementJob completed', ['processed' => $processed]);

        $this->completed($processed);
    }
}
