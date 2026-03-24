<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecruiterCommissionService
{
    public function processActivation(
        int $firmId,
        int $agentId,
        int $facilityId,
        string $triggerEvent
    ): void {
        $firm = DB::table('recruiter_firms')
            ->where('id', $firmId)
            ->where('status', 'ACTIVE')
            ->first();

        if (! $firm) {
            Log::warning('RecruiterCommissionService: firm not found or suspended', [
                'firm_id' => $firmId,
            ]);
            return;
        }

        // Check if this trigger event is active for this firm
        $trigger = DB::table('recruiter_commission_triggers')
            ->where('firm_id', $firmId)
            ->where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->first();

        if (! $trigger) {
            return;
        }

        $grossAmount = (float) $firm->commission_rate_kes;
        $cascadeConfig = json_decode($firm->cascade_config ?? '{}', true);

        // Calculate cascade breakdown
        $cascadeBreakdown = $this->calculateCascade(
            $agentId,
            $grossAmount,
            $cascadeConfig
        );

        $now = Carbon::now('UTC');

        // Create activation event
        $activationEventId = DB::table('recruiter_activation_events')->insertGetId([
            'firm_id'                => $firmId,
            'agent_id'               => $agentId,
            'facility_id'            => $facilityId,
            'trigger_event'          => $triggerEvent,
            'gross_amount_kes'       => $grossAmount,
            'cascade_breakdown'      => json_encode($cascadeBreakdown),
            'reconciliation_status'  => 'PENDING',
            'created_at'             => $now,
        ]);

        // Create ledger entries for firm and each node in cascade
        $this->writeLedgerEntries(
            $firmId,
            $agentId,
            $activationEventId,
            $grossAmount,
            $cascadeBreakdown,
            $now
        );

        Log::info('RecruiterCommissionService: activation processed', [
            'firm_id'           => $firmId,
            'agent_id'          => $agentId,
            'facility_id'       => $facilityId,
            'trigger_event'     => $triggerEvent,
            'gross_amount_kes'  => $grossAmount,
        ]);
    }

    public function checkNthOrderTrigger(int $facilityId): void
    {
        // Get order count for this facility
        $orderCount = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNotIn('status', ['CANCELLED'])
            ->whereNull('deleted_at')
            ->count();

        // Find any NTH_ORDER_PLACED triggers that match this count
        $triggers = DB::table('recruiter_commission_triggers')
            ->where('trigger_event', 'NTH_ORDER_PLACED')
            ->where('threshold_value', $orderCount)
            ->where('is_active', true)
            ->get();

        foreach ($triggers as $trigger) {
            // Find the agent attributed to this facility activation
            $activation = DB::table('recruiter_activation_events')
                ->where('facility_id', $facilityId)
                ->where('firm_id', $trigger->firm_id)
                ->orderBy('created_at')
                ->first();

            if ($activation) {
                $this->processActivation(
                    $trigger->firm_id,
                    $activation->agent_id,
                    $facilityId,
                    'NTH_ORDER_PLACED'
                );
            }
        }
    }

    private function calculateCascade(
        int $agentId,
        float $grossAmount,
        array $cascadeConfig
    ): array {
        $breakdown = [];

        $cascadeType = $cascadeConfig['type'] ?? 'firm_only';

        if ($cascadeType === 'firm_only') {
            return [['agent_id' => null, 'type' => 'firm', 'amount' => $grossAmount]];
        }

        if ($cascadeType === 'full_cascade') {
            // Walk up the agent tree
            $currentAgentId = $agentId;
            $remaining = $grossAmount;
            $level = 0;

            while ($currentAgentId) {
                $agent = DB::table('recruiter_agents')
                    ->where('id', $currentAgentId)
                    ->first();

                if (! $agent) break;

                $levelConfig = $cascadeConfig['levels'][$level] ?? null;
                $pct = $levelConfig['pct'] ?? (100 / max(1, count($cascadeConfig['levels'] ?? [1])));
                $amount = round($grossAmount * ($pct / 100), 2);

                $breakdown[] = [
                    'agent_id' => $currentAgentId,
                    'level'    => $level,
                    'amount'   => $amount,
                ];

                $remaining -= $amount;
                $currentAgentId = $agent->parent_agent_id;
                $level++;
            }

            // Any remainder goes to firm
            if ($remaining > 0) {
                $breakdown[] = ['agent_id' => null, 'type' => 'firm', 'amount' => $remaining];
            }
        }

        return $breakdown;
    }

    private function writeLedgerEntries(
        int $firmId,
        int $agentId,
        int $activationEventId,
        float $grossAmount,
        array $cascadeBreakdown,
        Carbon $now
    ): void {
        // Get current running balance for firm
        $currentBalance = (float) (DB::table('recruiter_ledger_entries')
            ->where('firm_id', $firmId)
            ->where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->value('running_balance_kes') ?? 0);

        $newBalance = $currentBalance + $grossAmount;

        DB::table('recruiter_ledger_entries')->insert([
            'firm_id'               => $firmId,
            'agent_id'              => $agentId,
            'activation_event_id'   => $activationEventId,
            'entry_type'            => 'ACCRUAL',
            'amount_kes'            => $grossAmount,
            'running_balance_kes'   => $newBalance,
            'note'                  => 'Commission accrual — rate: ' . $grossAmount . ' KES',
            'created_by'            => 0, // system
            'created_at'            => $now,
        ]);
    }
}
