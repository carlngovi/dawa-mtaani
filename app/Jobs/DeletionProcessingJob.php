<?php

namespace App\Jobs;

use App\Services\AnonymisationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeletionProcessingJob extends MonitoredJob
{
    public function __construct(
        private readonly int $deletionRequestId
    ) {}

    public function execute(): void
    {
        $request = DB::table('data_deletion_requests')
            ->where('id', $this->deletionRequestId)
            ->first();

        if (! $request) {
            Log::error('DeletionProcessingJob: request not found', [
                'id' => $this->deletionRequestId,
            ]);
            return;
        }

        DB::table('data_deletion_requests')
            ->where('id', $this->deletionRequestId)
            ->update([
                'status'                 => 'PROCESSING',
                'processing_started_at'  => Carbon::now('UTC'),
            ]);

        $service = app(AnonymisationService::class);
        $batchId = (string) Str::uuid();
        $totalAnonymised = 0;
        $totalDeleted = 0;

        try {
            // Anonymise financial records — never hard delete
            $totalAnonymised += $service->anonymiseForDeletion(
                $request->facility_id,
                $batchId
            );

            // Hard delete non-financial personal data
            $totalDeleted += DB::table('facility_flags')
                ->where('facility_id', $request->facility_id)
                ->delete();

            $totalDeleted += DB::table('facility_restock_subscriptions')
                ->where('facility_id', $request->facility_id)
                ->delete();

            $totalDeleted += DB::table('facility_favourite_products')
                ->where('facility_id', $request->facility_id)
                ->delete();

            DB::table('data_deletion_requests')
                ->where('id', $this->deletionRequestId)
                ->update([
                    'status'             => 'COMPLETED',
                    'completed_at'       => Carbon::now('UTC'),
                    'records_anonymised' => $totalAnonymised,
                    'records_deleted'    => $totalDeleted,
                ]);

            Log::info('DeletionProcessingJob: completed', [
                'request_id'  => $this->deletionRequestId,
                'facility_id' => $request->facility_id,
                'anonymised'  => $totalAnonymised,
                'deleted'     => $totalDeleted,
            ]);

        } catch (\Throwable $e) {
            Log::error('DeletionProcessingJob: failed', [
                'request_id' => $this->deletionRequestId,
                'error'      => $e->getMessage(),
            ]);

            DB::table('data_deletion_requests')
                ->where('id', $this->deletionRequestId)
                ->update(['status' => 'PENDING']);

            throw $e;
        }

        $this->completed($totalAnonymised + $totalDeleted);
    }
}
