<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class MonitoredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $monitoringId = null;

    protected int $startedAt = 0;

    public int $tries = 3;

    public int $timeout = 300;

    public function handle(): void
    {
        $this->starting();

        try {
            $this->execute();
            $this->completed();
        } catch (Throwable $e) {
            $this->failed($e);
            throw $e;
        }
    }

    abstract protected function execute(): void;

    protected function starting(): void
    {
        $this->startedAt = time();

        try {
            $this->monitoringId = DB::table('job_monitoring')->insertGetId([
                'job_name' => get_class($this),
                'status' => 'STARTED',
                'started_at' => Carbon::now('UTC'),
                'created_at' => Carbon::now('UTC'),
            ]);
        } catch (Throwable $e) {
            Log::warning('MonitoredJob: failed to insert monitoring row', [
                'job' => get_class($this),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function completed(int $recordsProcessed = 0): void
    {
        if ($this->monitoringId === null) {
            return;
        }

        try {
            DB::table('job_monitoring')
                ->where('id', $this->monitoringId)
                ->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now('UTC'),
                    'duration_ms' => (time() - $this->startedAt) * 1000,
                    'records_processed' => $recordsProcessed,
                ]);
        } catch (Throwable $e) {
            Log::warning('MonitoredJob: failed to update monitoring row on completion', [
                'job' => get_class($this),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function failed(Throwable $e): void
    {
        if ($this->monitoringId === null) {
            return;
        }

        try {
            DB::table('job_monitoring')
                ->where('id', $this->monitoringId)
                ->update([
                    'status' => 'FAILED',
                    'completed_at' => Carbon::now('UTC'),
                    'error_message' => substr($e->getMessage(), 0, 1000),
                ]);
        } catch (Throwable $ex) {
            Log::warning('MonitoredJob: failed to update monitoring row on failure', [
                'job' => get_class($this),
                'error' => $ex->getMessage(),
            ]);
        }
    }
}
