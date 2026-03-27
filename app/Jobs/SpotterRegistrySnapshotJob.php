<?php

namespace App\Jobs;

use App\Models\SpotterSubmission;
use Illuminate\Support\Facades\Cache;

class SpotterRegistrySnapshotJob extends MonitoredJob
{
    protected function execute(): void
    {
        $counties = SpotterSubmission::distinct()->pluck('county');
        $count = 0;

        foreach ($counties as $county) {
            $snapshot = SpotterSubmission::where('county', $county)
                ->where('status', 'accepted')
                ->select('pharmacy', 'ward')
                ->get()
                ->toArray();

            Cache::put("spotter_registry_{$county}", $snapshot, 3600);
            $count++;
        }

        $this->completed($count);
    }
}
