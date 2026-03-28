<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnonymisationService
{
    private string $salt;
    private ?string $previousSalt;

    public function __construct()
    {
        $this->salt = env('DATA_ANONYMISATION_SALT', '');
        $this->previousSalt = env('DATA_ANONYMISATION_SALT_PREVIOUS') ?: null;

        if (empty($this->salt)) {
            Log::warning('AnonymisationService: DATA_ANONYMISATION_SALT is not set in .env');
        }
    }

    // ----------------------------------------------------------
    // Public API
    // ----------------------------------------------------------

    public function anonymise(string $dataCategory, int $batchSize = 500): int
    {
        $batchId = (string) Str::uuid();
        $startedAt = Carbon::now('UTC');
        $processed = 0;

        try {
            $processed = match ($dataCategory) {
                'facilities'       => $this->anonymiseFacilities($batchSize),
                'customer_orders'  => $this->anonymiseCustomerOrders($batchSize),
                'pharmacy_groups'  => $this->anonymisePharmacyGroups($batchSize),
                'recruiter_agents' => $this->anonymiseRecruiterAgents($batchSize),
                'whatsapp_messages' => $this->anonymiseWhatsappMessages($batchSize),
                default => 0,
            };

            $this->logBatch($batchId, $dataCategory, $processed, $startedAt, 'RETENTION_SCHEDULE');
        } catch (\Throwable $e) {
            Log::error('AnonymisationService: anonymisation failed', [
                'category' => $dataCategory,
                'error'    => $e->getMessage(),
            ]);
            $this->logBatch($batchId, $dataCategory, $processed, $startedAt, 'RETENTION_SCHEDULE');
        }

        return $processed;
    }

    public function anonymiseForDeletion(int $facilityId, string $batchId): int
    {
        $startedAt = Carbon::now('UTC');
        $processed = 0;

        try {
            $processed += $this->anonymiseFacilityRecord($facilityId);
            $this->logBatch($batchId, 'facility_deletion', $processed, $startedAt, 'DELETION_REQUEST');
        } catch (\Throwable $e) {
            Log::error('AnonymisationService: deletion anonymisation failed', [
                'facility_id' => $facilityId,
                'error'       => $e->getMessage(),
            ]);
        }

        return $processed;
    }

    public function anonymiseCustomerForDeletion(string $customerPhoneHash, string $batchId): int
    {
        $startedAt = Carbon::now('UTC');
        $processed = 0;

        try {
            // Anonymise customer_orders where customer_phone matches
            // We store hashed phone — match against hash
            $processed += DB::table('customer_orders')
                ->whereRaw('SHA2(CONCAT(customer_phone, ?), 256) = ?', [
                    $this->salt,
                    $customerPhoneHash,
                ])
                ->where('is_anonymised', false)
                ->update([
                    'customer_phone'   => $this->hash('ANONYMISED'),
                    'customer_name'    => null,
                    'is_anonymised'    => true,
                    'anonymised_at'    => Carbon::now('UTC'),
                ]);

            $this->logBatch($batchId, 'customer_deletion', $processed, $startedAt, 'DELETION_REQUEST');
        } catch (\Throwable $e) {
            Log::error('AnonymisationService: customer deletion failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $processed;
    }

    // ----------------------------------------------------------
    // Hash helper
    // ----------------------------------------------------------

    public function hash(string $value): string
    {
        if (empty($this->salt)) {
            throw new \RuntimeException(
                'DATA_ANONYMISATION_SALT is not configured. Cannot anonymise data.'
            );
        }

        return hash('sha256', $value . $this->salt);
    }

    // ----------------------------------------------------------
    // Per-category anonymisation
    // ----------------------------------------------------------

    private function anonymiseFacilities(int $batchSize): int
    {
        $retentionYears = DB::table('data_retention_policies')
            ->where('data_category', 'personal_data')
            ->value('retention_years') ?? 2;

        $cutoff = Carbon::now('UTC')->subYears($retentionYears);

        $facilities = DB::table('facilities')
            ->where('updated_at', '<=', $cutoff)
            ->where('is_anonymised', false)
            ->limit($batchSize)
            ->get();

        $count = 0;
        foreach ($facilities as $facility) {
            DB::table('facilities')
                ->where('id', $facility->id)
                ->update([
                    'owner_name'             => $this->hash($facility->owner_name ?? ''),
                    'phone'                  => $this->hash($facility->phone ?? ''),
                    'email'                  => $facility->email
                                                ? $this->hash($facility->email)
                                                : null,
                    'banking_account_number' => $facility->banking_account_number
                                                ? $this->hash($facility->banking_account_number)
                                                : null,
                    'is_anonymised'          => true,
                    'anonymised_at'          => Carbon::now('UTC'),
                ]);
            $count++;
        }

        return $count;
    }

    private function anonymiseFacilityRecord(int $facilityId): int
    {
        $facility = DB::table('facilities')->where('id', $facilityId)->first();

        if (! $facility || $facility->is_anonymised) {
            return 0;
        }

        DB::table('facilities')
            ->where('id', $facilityId)
            ->update([
                'owner_name'             => $this->hash($facility->owner_name ?? ''),
                'phone'                  => $this->hash($facility->phone ?? ''),
                'email'                  => $facility->email
                                            ? $this->hash($facility->email)
                                            : null,
                'banking_account_number' => $facility->banking_account_number
                                            ? $this->hash($facility->banking_account_number)
                                            : null,
                'is_anonymised'          => true,
                'anonymised_at'          => Carbon::now('UTC'),
            ]);

        return 1;
    }

    private function anonymiseCustomerOrders(int $batchSize): int
    {
        $cutoff = Carbon::now('UTC')->subYears(2);

        return DB::table('customer_orders')
            ->where('created_at', '<=', $cutoff)
            ->where('is_anonymised', false)
            ->limit($batchSize)
            ->update([
                'customer_phone' => DB::raw('SHA2(CONCAT(customer_phone, "' . $this->salt . '"), 256)'),
                'customer_name'  => null,
                'is_anonymised'  => true,
                'anonymised_at'  => Carbon::now('UTC'),
            ]);
    }

    private function anonymisePharmacyGroups(int $batchSize): int
    {
        $cutoff = Carbon::now('UTC')->subYears(2);

        $groups = DB::table('pharmacy_groups')
            ->where('updated_at', '<=', $cutoff)
            ->where('is_anonymised', false)
            ->limit($batchSize)
            ->get();

        $count = 0;
        foreach ($groups as $group) {
            DB::table('pharmacy_groups')
                ->where('id', $group->id)
                ->update([
                    'group_owner_name'  => $this->hash($group->group_owner_name ?? ''),
                    'group_owner_phone' => $this->hash($group->group_owner_phone ?? ''),
                    'group_owner_email' => $group->group_owner_email
                                          ? $this->hash($group->group_owner_email)
                                          : null,
                    'is_anonymised'     => true,
                    'anonymised_at'     => Carbon::now('UTC'),
                ]);
            $count++;
        }

        return $count;
    }

    private function anonymiseRecruiterAgents(int $batchSize): int
    {
        $cutoff = Carbon::now('UTC')->subYears(2);

        $agents = DB::table('recruiter_agents')
            ->where('created_at', '<=', $cutoff)
            ->where('is_anonymised', false)
            ->limit($batchSize)
            ->get();

        $count = 0;
        foreach ($agents as $agent) {
            DB::table('recruiter_agents')
                ->where('id', $agent->id)
                ->update([
                    'agent_name'    => $this->hash($agent->agent_name ?? ''),
                    'agent_phone'   => $this->hash($agent->agent_phone ?? ''),
                    'is_anonymised' => true,
                    'anonymised_at' => Carbon::now('UTC'),
                ]);
            $count++;
        }

        return $count;
    }

    private function anonymiseWhatsappMessages(int $batchSize): int
    {
        $cutoff = Carbon::now('UTC')->subYear();

        return DB::table('whatsapp_messages')
            ->where('created_at', '<=', $cutoff)
            ->limit($batchSize)
            ->delete();
    }

    // ----------------------------------------------------------
    // Audit log
    // ----------------------------------------------------------

    private function logBatch(
        string $batchId,
        string $dataCategory,
        int $recordsProcessed,
        Carbon $startedAt,
        string $triggeredBy
    ): void {
        try {
            DB::table('anonymisation_log')->insert([
                'batch_id'          => $batchId,
                'data_category'     => $dataCategory,
                'records_processed' => $recordsProcessed,
                'started_at'        => $startedAt,
                'completed_at'      => Carbon::now('UTC'),
                'triggered_by'      => $triggeredBy,
            ]);
        } catch (\Throwable $e) {
            Log::error('AnonymisationService: failed to write anonymisation log', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
