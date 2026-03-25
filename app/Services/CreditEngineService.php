<?php

namespace App\Services;

use App\Models\CreditTranche;
use App\Models\CreditTier;
use App\Models\FacilityCreditAccount;
use App\Models\FacilityTrancheBalance;
use App\Models\CreditEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * CreditEngineService
 *
 * Core credit operations: availability check, draw, repayment, suspension.
 * All parameters come from database configuration — nothing is hardcoded.
 * All monetary operations run inside DB::transaction().
 */
class CreditEngineService
{
    /**
     * Check whether a facility has sufficient credit available for a given amount.
     * Returns ['available' => bool, 'balance' => float, 'reason' => string|null]
     */
    public function checkAvailability(int $facilityId, float $amount): array
    {
        $account = FacilityCreditAccount::where('facility_id', $facilityId)
            ->where('account_status', 'ACTIVE')
            ->first();

        if (! $account) {
            return ['available' => false, 'balance' => 0, 'reason' => 'No active credit account'];
        }

        $tranche = $account->tranche;
        if (! $tranche || ! $tranche->is_active) {
            return ['available' => false, 'balance' => 0, 'reason' => 'Credit tranche inactive'];
        }

        // Check PPB licence expiry — blocks credit draws per spec
        $facility = $account->facility;
        if ($facility && $facility->ppb_licence_status === 'EXPIRED') {
            return ['available' => false, 'balance' => 0, 'reason' => 'PPB licence expired'];
        }

        $available = FacilityTrancheBalance::where('credit_account_id', $account->id)
            ->sum('available_amount');

        return [
            'available' => $available >= $amount,
            'balance'   => (float) $available,
            'reason'    => $available < $amount ? 'Insufficient credit balance' : null,
        ];
    }

    /**
     * Draw credit for an order. Deducts from available_amount across tiers
     * in sort_order (lowest tier first). Creates a CreditEvent for audit.
     * Must be called inside an existing DB transaction from OrderPlacementService.
     */
    public function drawCredit(int $facilityId, float $amount, string $reference): void
    {
        $account = FacilityCreditAccount::where('facility_id', $facilityId)
            ->where('account_status', 'ACTIVE')
            ->lockForUpdate()
            ->firstOrFail();

        $remaining = $amount;

        $balances = FacilityTrancheBalance::where('credit_account_id', $account->id)
            ->where('available_amount', '>', 0)
            ->join('credit_tiers', 'credit_tiers.id', '=', 'facility_tranche_balances.tier_id')
            ->orderBy('credit_tiers.sort_order')
            ->select('facility_tranche_balances.*')
            ->lockForUpdate()
            ->get();

        foreach ($balances as $balance) {
            if ($remaining <= 0) break;

            $draw = min($remaining, (float) $balance->available_amount);

            $balance->update([
                'drawn_amount'     => $balance->drawn_amount + $draw,
                'available_amount' => $balance->available_amount - $draw,
                'last_drawn_at'    => now(),
            ]);

            CreditEvent::create([
                'credit_account_id' => $account->id,
                'tier_id'           => $balance->tier_id,
                'event_type'        => 'DRAW',
                'amount'            => $draw,
                'running_balance'   => $balance->available_amount - $draw,
                'reference'         => $reference,
                'triggered_by'      => auth()->id(),
                'occurred_at'       => now(),
            ]);

            $remaining -= $draw;
        }

        if ($remaining > 0) {
            throw new RuntimeException("Insufficient credit to complete draw. Shortfall: {$remaining}");
        }
    }

    /**
     * Repay credit — restores available_amount. Applies to the tier with the
     * highest drawn balance first (most recent draw). Creates a CreditEvent.
     */
    public function repayCredit(int $facilityId, float $amount, string $reference): void
    {
        DB::transaction(function () use ($facilityId, $amount, $reference) {
            $account = FacilityCreditAccount::where('facility_id', $facilityId)
                ->lockForUpdate()
                ->firstOrFail();

            $remaining = $amount;

            $balances = FacilityTrancheBalance::where('credit_account_id', $account->id)
                ->where('drawn_amount', '>', 0)
                ->lockForUpdate()
                ->orderByDesc('drawn_amount')
                ->get();

            foreach ($balances as $balance) {
                if ($remaining <= 0) break;

                $repay = min($remaining, (float) $balance->drawn_amount);

                $balance->update([
                    'drawn_amount'     => $balance->drawn_amount - $repay,
                    'available_amount' => $balance->available_amount + $repay,
                    'last_repaid_at'   => now(),
                ]);

                CreditEvent::create([
                    'credit_account_id' => $account->id,
                    'tier_id'           => $balance->tier_id,
                    'event_type'        => 'REPAYMENT',
                    'amount'            => $repay,
                    'running_balance'   => $balance->available_amount + $repay,
                    'reference'         => $reference,
                    'triggered_by'      => auth()->id(),
                    'occurred_at'       => now(),
                ]);

                $remaining -= $repay;
            }
        });
    }

    /**
     * Suspend a facility's credit account. Logs reason. Used by payment
     * escalation and admin manual override.
     */
    public function suspendAccount(int $facilityId, string $reason): void
    {
        DB::transaction(function () use ($facilityId, $reason) {
            FacilityCreditAccount::where('facility_id', $facilityId)
                ->update([
                    'account_status'   => 'SUSPENDED',
                    'suspended_at'     => now(),
                    'suspension_reason' => $reason,
                ]);
        });
    }
}
