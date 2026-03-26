<?php

use App\Jobs\AnomalyDetectionJob;
use App\Jobs\BaselineCalculationJob;
use App\Jobs\DisputeSlaMonitorJob;
use App\Jobs\JobAlertJob;
use App\Jobs\NetworkSummaryRefreshJob;
use App\Jobs\PriceListExpiryJob;
use App\Jobs\PpbReverificationJob;
use App\Jobs\RetentionEnforcementJob;
use App\Jobs\BasketAbandonmentJob;
use App\Jobs\CopayEscalationJob;
use App\Jobs\FacilitySettlementJob;
use App\Jobs\OnlineStoreEligibilityJob;
use App\Jobs\SloComplianceJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new BaselineCalculationJob)->dailyAt('01:00')->timezone('Africa/Nairobi');
Schedule::job(new AnomalyDetectionJob)->everyFifteenMinutes();
Schedule::job(new SloComplianceJob)->dailyAt('00:30')->timezone('Africa/Nairobi');
Schedule::job(new JobAlertJob)->everyFiveMinutes();
Schedule::job(new RetentionEnforcementJob)->dailyAt('02:00')->timezone('Africa/Nairobi');
Schedule::job(new PpbReverificationJob)->daily()->timezone('Africa/Nairobi');
Schedule::job(new DisputeSlaMonitorJob)->everyThirtyMinutes();
Schedule::job(new PriceListExpiryJob)->dailyAt('00:00')->timezone('Africa/Nairobi');
Schedule::job(new NetworkSummaryRefreshJob)->everyFifteenMinutes();
Schedule::job(new OnlineStoreEligibilityJob)->dailyAt('02:00')->timezone('Africa/Nairobi')->onOneServer();
Schedule::job(new BasketAbandonmentJob)->hourly()->onOneServer();
Schedule::job(new FacilitySettlementJob)->dailyAt('01:00')->onOneServer();
Schedule::job(new CopayEscalationJob)->hourly()->onOneServer();
