<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Jobs;

use App\Models\SpotterAttendance;

class SpotterAttendanceAutoCloseJob extends MonitoredJob
{
    protected function execute(): void
    {
        $count = SpotterAttendance::whereNull('clock_out_at')
            ->where('date', now()->toDateString())
            ->update([
                'clock_out_at' => now(),
                'auto_closed' => true,
            ]);

        $this->completed($count);
    }
}
