<?php

namespace App\Jobs;

use App\Models\CustomerCounterfeitReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyPpbOfCounterfeitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public CustomerCounterfeitReport $report)
    {
        $this->onQueue('quality-flags');
    }

    public function handle(): void
    {
        Log::info('PPB notification queued for counterfeit report', [
            'report_id' => $this->report->id,
        ]);

        // TODO: Wire to PPB notification API in Module 21 API Integration Layer
    }
}
