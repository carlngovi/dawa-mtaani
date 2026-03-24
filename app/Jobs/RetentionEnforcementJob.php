<?php

namespace App\Jobs;

use App\Services\AnonymisationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetentionEnforcementJob extends MonitoredJob
{
    public function execute(): void
    {
        $processed = 0;

        $policies = DB::table('data_retention_policies')
            ->where('is_active', true)
            ->get();

        $service = app(AnonymisationService::class);

        foreach ($policies as $policy) {
            try {
                if ($policy->action_on_expiry === 'ANONYMISE') {
                    $count = $service->anonymise($policy->data_category);
                    $processed += $count;

                    Log::info('RetentionEnforcementJob: anonymised records', [
                        'category' => $policy->data_category,
                        'count'    => $count,
                    ]);
                } elseif ($policy->action_on_expiry === 'DELETE') {
                    $count = $this->hardDelete($policy->data_category, $policy->retention_years);
                    $processed += $count;

                    Log::info('RetentionEnforcementJob: deleted records', [
                        'category' => $policy->data_category,
                        'count'    => $count,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('RetentionEnforcementJob: failed for category', [
                    'category' => $policy->data_category,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $this->completed($processed);
    }

    private function hardDelete(string $dataCategory, int $retentionYears): int
    {
        $cutoff = now('UTC')->subYears($retentionYears);

        return match ($dataCategory) {
            'whatsapp_messages' => DB::table('whatsapp_messages')
                ->where('created_at', '<=', $cutoff)
                ->delete(),
            'security_events' => DB::table('security_events')
                ->where('created_at', '<=', $cutoff)
                ->delete(),
            'session_fingerprints' => DB::table('session_fingerprints')
                ->where('created_at', '<=', $cutoff)
                ->delete(),
            default => 0,
        };
    }
}
