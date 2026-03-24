<?php

namespace App\Jobs;

use App\DTOs\PpbVerificationResult;
use App\Services\PpbVerificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpbReverificationJob extends MonitoredJob
{
    public function execute(): void
    {
        $cutoff = Carbon::now('UTC')->subDays(30);
        $processed = 0;

        $facilities = DB::table('facilities')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('ppb_verified_at')
                  ->orWhere('ppb_verified_at', '<', $cutoff);
            })
            ->whereNotIn('facility_status', ['CHURNED'])
            ->select(['id', 'ulid', 'ppb_licence_number', 'facility_status', 'facility_name'])
            ->get();

        $service = app(PpbVerificationService::class);

        foreach ($facilities as $facility) {
            try {
                $result = $service->verify($facility->ppb_licence_number);

                $this->processResult($facility, $result);
                $processed++;

            } catch (\Throwable $e) {
                Log::error('PpbReverificationJob: failed for facility', [
                    'facility_id' => $facility->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $this->completed($processed);
    }

    private function processResult(object $facility, PpbVerificationResult $result): void
    {
        $now = Carbon::now('UTC');

        // Log the verification
        DB::table('ppb_verification_logs')->insert([
            'facility_id'              => $facility->id,
            'checked_at'               => $now,
            'licence_status_returned'  => $result->licenceStatus ?? 'NOT_FOUND',
            'response_json'            => json_encode([
                'found'            => $result->found,
                'facility_name'    => $result->facilityName,
                'ppb_type'         => $result->ppbType,
                'licence_status'   => $result->licenceStatus,
                'message'          => $result->message,
            ]),
            'triggered_by'             => 'SCHEDULED',
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        // Update ppb_verified_at
        DB::table('facilities')
            ->where('id', $facility->id)
            ->update(['ppb_verified_at' => $now]);

        // Handle expired or suspended licences
        if ($result->found && in_array($result->licenceStatus, ['EXPIRED', 'SUSPENDED'])) {
            DB::table('facilities')
                ->where('id', $facility->id)
                ->update([
                    'ppb_licence_status' => $result->licenceStatus,
                    'facility_status'    => 'SUSPENDED',
                ]);

            // Alert network admin
            DB::table('audit_logs')->insert([
                'facility_id'   => $facility->id,
                'user_id'       => null,
                'action'        => 'facility_suspended_ppb_' . strtolower($result->licenceStatus),
                'model_type'    => 'App\Models\Facility',
                'model_id'      => $facility->id,
                'payload_after' => json_encode([
                    'ppb_licence_status' => $result->licenceStatus,
                    'facility_status'    => 'SUSPENDED',
                    'reason'             => 'PPB licence ' . $result->licenceStatus,
                ]),
                'ip_address'    => '0.0.0.0',
                'created_at'    => $now,
            ]);

            Log::warning('PpbReverificationJob: facility suspended due to PPB status', [
                'facility_id'    => $facility->id,
                'facility_name'  => $facility->facility_name,
                'licence_status' => $result->licenceStatus,
            ]);
        }

        // Handle valid licence — reactivate if previously suspended for PPB reasons
        if ($result->found && $result->licenceStatus === 'VALID') {
            DB::table('facilities')
                ->where('id', $facility->id)
                ->update(['ppb_licence_status' => 'VALID']);
        }
    }
}
