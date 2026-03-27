<?php

namespace App\Jobs;

use App\Models\SpotterFollowUp;
use Illuminate\Support\Facades\Log;

class SpotterFollowUpOverdueJob extends MonitoredJob
{
    protected function execute(): void
    {
        $count = 0;

        SpotterFollowUp::where('status', 'open')
            ->where('follow_up_date', '<', now()->subHours(48))
            ->whereNull('overdue_alerted_at')
            ->each(function ($followUp) use (&$count) {
                $followUp->update([
                    'status' => 'overdue',
                    'overdue_alerted_at' => now(),
                ]);

                // TODO: Send SpotterFollowUpOverdueNotification to sales rep once spotter↔rep relationship is established
                Log::warning('Spotter follow-up overdue', [
                    'follow_up_id' => $followUp->id,
                    'submission_id' => $followUp->spotter_submission_id,
                    'follow_up_date' => $followUp->follow_up_date->toDateString(),
                ]);

                $count++;
            });

        $this->completed($count);
    }
}
