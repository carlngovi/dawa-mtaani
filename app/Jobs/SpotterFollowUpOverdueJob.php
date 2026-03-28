<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Jobs;

use App\Models\SpotterFollowUp;
use App\Models\SpotterProfile;
use App\Notifications\SpotterFollowUpOverdueNotification;
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

                // Notify the assigned sales rep
                $submission = $followUp->submission;
                $spotterProfile = $submission
                    ? SpotterProfile::where('user_id', $submission->spotter_user_id)->first()
                    : null;
                $salesRepUser = $spotterProfile?->salesRep;

                if ($salesRepUser) {
                    $salesRepUser->notify(new SpotterFollowUpOverdueNotification($followUp));
                }

                Log::info('Spotter follow-up marked overdue', [
                    'follow_up_id' => $followUp->id,
                    'submission_id' => $followUp->spotter_submission_id,
                    'follow_up_date' => $followUp->follow_up_date->toDateString(),
                    'sales_rep_notified' => $salesRepUser?->email,
                ]);

                $count++;
            });

        $this->completed($count);
    }
}
