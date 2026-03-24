<?php

namespace App\Jobs;

use App\Services\JobAlertService;

class JobAlertJob extends MonitoredJob
{
    public function execute(): void
    {
        $service = app(JobAlertService::class);
        $service->check();
        $this->completed();
    }
}
