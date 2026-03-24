<?php

namespace App\Jobs;

use App\Models\OnlineStoreEligibleFacility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineStoreEligibilityJob extends MonitoredJob
{
    public $queue = 'default';

    protected function execute(): void
    {
        $minPosDays = (int) (DB::table('system_settings')
            ->where('key', 'min_pos_days')
            ->value('value') ?: 90);

        $maxVariance = (float) (DB::table('system_settings')
            ->where('key', 'max_variance_score')
            ->value('value') ?: 0.30);

        $facilities = DB::table('facilities')
            ->where('facility_status', 'ACTIVE')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dispensing_entries')
                    ->whereColumn('dispensing_entries.facility_id', 'facilities.id');
            })
            ->get();

        $processed = 0;

        foreach ($facilities as $facility) {
            $posDataDays = (int) DB::table('dispensing_entries')
                ->where('facility_id', $facility->id)
                ->selectRaw('COUNT(DISTINCT DATE(dispensed_at)) as days')
                ->value('days');

            $stats = DB::table('dispensing_entries')
                ->where('facility_id', $facility->id)
                ->selectRaw('STDDEV(quantity) / NULLIF(AVG(quantity), 0) as variance_score')
                ->first();

            $varianceScore = $stats->variance_score !== null
                ? round((float) $stats->variance_score, 2)
                : 0.00;

            $qualifies = $posDataDays >= $minPosDays && $varianceScore < $maxVariance;

            OnlineStoreEligibleFacility::updateOrCreate(
                ['facility_id' => $facility->id],
                [
                    'qualified_at'      => $qualifies ? Carbon::now('UTC') : null,
                    'pos_data_days'     => $posDataDays,
                    'variance_score'    => $varianceScore,
                    'branding_mode'     => $facility->branding_mode ?? 'OWN_BRAND',
                    'is_network_member' => $facility->network_membership === 'NETWORK',
                    'is_active'         => $qualifies,
                ]
            );

            $processed++;
        }

        Log::info('OnlineStoreEligibilityJob completed', ['processed' => $processed]);

        $this->completed($processed);
    }
}
