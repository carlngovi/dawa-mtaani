<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NetworkSummaryRefreshJob extends MonitoredJob
{
    public function execute(): void
    {
        // Prevent overlap using atomic Redis lock
        $lock = Cache::lock('network_summary_refresh', 300);

        if (! $lock->get()) {
            Log::info('NetworkSummaryRefreshJob: skipped — another instance is running');
            $this->completed(0);
            return;
        }

        try {
            $processed = 0;
            $today = Carbon::now('UTC')->toDateString();
            $now = Carbon::now('UTC');

            $memberships = ['NETWORK', 'OFF_NETWORK', 'ALL'];

            foreach ($memberships as $membership) {
                $counties = DB::table('facilities')
                    ->whereNull('deleted_at')
                    ->distinct()
                    ->pluck('county');

                // Add null for national-level summary
                $counties->push(null);

                foreach ($counties as $county) {
                    $facilityTypes = ['RETAIL', 'WHOLESALE', 'HOSPITAL', 'MANUFACTURER', null];

                    foreach ($facilityTypes as $facilityType) {
                        try {
                            $summary = $this->computeSummary($today, $county, $membership, $facilityType);

                            DB::table('network_daily_summaries')->upsert(
                                [array_merge($summary, [
                                    'summary_date'       => $today,
                                    'county'             => $county,
                                    'network_membership' => $membership,
                                    'facility_type'      => $facilityType,
                                    'computed_at'        => $now,
                                    'created_at'         => $now,
                                    'updated_at'         => $now,
                                ])],
                                ['summary_date', 'county', 'network_membership', 'facility_type'],
                                [
                                    'total_orders', 'total_gmv', 'avg_order_value',
                                    'active_facilities', 'new_facilities',
                                    'credit_drawn', 'credit_repaid',
                                    'overdue_count', 'overdue_value',
                                    'computed_at', 'updated_at',
                                ]
                            );

                            $processed++;
                        } catch (\Throwable $e) {
                            Log::warning('NetworkSummaryRefreshJob: segment failed', [
                                'county'     => $county,
                                'membership' => $membership,
                                'type'       => $facilityType,
                                'error'      => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            $this->completed($processed);
        } finally {
            $lock->release();
        }
    }

    private function computeSummary(
        string $date,
        ?string $county,
        string $membership,
        ?string $facilityType
    ): array {
        $orderQuery = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereDate('o.created_at', $date)
            ->whereNull('o.deleted_at');

        if ($county) {
            $orderQuery->where('f.county', $county);
        }

        if ($membership !== 'ALL') {
            $orderQuery->where('f.network_membership', $membership);
        }

        if ($facilityType) {
            $orderQuery->where('f.ppb_facility_type', $facilityType);
        }

        $totalOrders = (clone $orderQuery)->count();
        $totalGmv = (clone $orderQuery)->sum('o.total_amount') ?? 0;
        $avgOrderValue = $totalOrders > 0 ? round($totalGmv / $totalOrders, 2) : 0;

        // Active facilities — placed at least one order today
        $activeFacilities = (clone $orderQuery)
            ->distinct('o.retail_facility_id')
            ->count('o.retail_facility_id');

        // New facilities — activated today
        $newFacilityQuery = DB::table('facilities')
            ->whereDate('activated_at', $date)
            ->whereNull('deleted_at');

        if ($county) {
            $newFacilityQuery->where('county', $county);
        }

        if ($membership !== 'ALL') {
            $newFacilityQuery->where('network_membership', $membership);
        }

        if ($facilityType) {
            $newFacilityQuery->where('ppb_facility_type', $facilityType);
        }

        $newFacilities = $newFacilityQuery->count();

        // Credit drawn today
        $creditDrawn = (clone $orderQuery)->sum('o.credit_amount') ?? 0;

        return [
            'total_orders'      => $totalOrders,
            'total_gmv'         => round($totalGmv, 2),
            'avg_order_value'   => $avgOrderValue,
            'active_facilities' => $activeFacilities,
            'new_facilities'    => $newFacilities,
            'credit_drawn'      => round($creditDrawn, 2),
            'credit_repaid'     => 0, // Populated when credit module is built
            'overdue_count'     => 0, // Populated when credit module is built
            'overdue_value'     => 0, // Populated when credit module is built
        ];
    }
}
