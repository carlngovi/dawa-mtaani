<?php
namespace App\Services;

use App\Models\CreditTranche;
use App\Models\CreditTier;
use App\Models\CreditEvent;
use App\Models\CreditProgressionRule;
use App\Models\CreditTrancheParty;
use App\Models\FacilityCreditAccount;
use App\Models\FacilityTrancheBalance;
use App\Models\Facility;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditEngineService
{
    private const CACHE_KEY = 'credit_engine_config';
    private const CACHE_TTL = 300;

    public function checkAvailability(int $facilityId, array $cartItems): array
    {
        $facility = Facility::findOrFail($facilityId);
        if ($facility->network_membership === 'OFF_NETWORK') {
            throw new \RuntimeException('Credit engine cannot run for OFF_NETWORK facilities.');
        }

        $account = FacilityCreditAccount::where('facility_id', $facilityId)->first();
        if (! $account || $account->account_status !== 'ACTIVE') {
            return [
                'approved' => [], 'blocked' => $cartItems, 'draw_plan' => [],
                'reason' => 'Credit account is not active.',
            ];
        }

        $config = $this->loadConfig();
        $approved = [];
        $blocked = [];
        $drawPlan = [];

        DB::transaction(function () use ($facilityId, $cartItems, $config, &$approved, &$blocked, &$drawPlan) {
            $balances = FacilityTrancheBalance::where('facility_id', $facilityId)
                ->lockForUpdate()->get()->keyBy('tranche_id');

            foreach ($cartItems as $item) {
                $allocated = false;

                foreach ($config['tranches'] as $tranche) {
                    if (! $this->productAllowedInTranche($item, $tranche)) continue;

                    $balance = $balances->get($tranche['id']);
                    if (! $balance) continue;

                    $available = $balance->current_balance;
                    $lineTotal = (float) $item['line_total'];

                    if ($available >= $lineTotal) {
                        $approved[] = array_merge($item, [
                            'tranche_id' => $tranche['id'],
                            'tier_id' => $this->resolveTier($item, $tranche, $balance),
                        ]);
                        $drawPlan[$tranche['id']] = ($drawPlan[$tranche['id']] ?? 0) + $lineTotal;
                        $allocated = true;
                        break;
                    }
                }

                if (! $allocated) {
                    $blocked[] = array_merge($item, ['reason' => 'Insufficient credit balance.']);
                }
            }
        });

        return compact('approved', 'blocked', 'drawPlan');
    }

    public function drawCredit(int $orderId, array $drawPlan): void
    {
        DB::transaction(function () use ($orderId, $drawPlan) {
            foreach ($drawPlan as $trancheId => $amount) {
                $balance = FacilityTrancheBalance::where('tranche_id', $trancheId)
                    ->lockForUpdate()->firstOrFail();

                $balanceBefore = (float) $balance->current_balance;
                $balanceAfter = $balanceBefore - (float) $amount;

                $balance->update(['current_balance' => $balanceAfter]);

                CreditEvent::create([
                    'ulid' => Str::ulid(), 'facility_id' => $balance->facility_id,
                    'tranche_id' => $trancheId, 'order_id' => $orderId,
                    'event_type' => 'DRAW', 'amount' => $amount,
                    'balance_before' => $balanceBefore, 'balance_after' => $balanceAfter,
                    'notes' => ['order_id' => $orderId],
                ]);
            }
        });
    }

    public function processRepayment(int $facilityId, int $trancheId, float $amount, int $daysElapsed): void
    {
        DB::transaction(function () use ($facilityId, $trancheId, $amount, $daysElapsed) {
            $balance = FacilityTrancheBalance::where('facility_id', $facilityId)
                ->where('tranche_id', $trancheId)->lockForUpdate()->firstOrFail();

            $tranche = CreditTranche::with('activeTiers')->findOrFail($trancheId);

            $rule = CreditProgressionRule::where('is_suspension_trigger', false)
                ->where('max_days_to_qualify', '>=', $daysElapsed)
                ->orderBy('max_days_to_qualify')->first();

            $progressionAmount = 0;
            if ($rule) {
                $progressionAmount = round((float) $balance->current_balance * ($rule->progression_rate_pct / 100), 2);
            }

            $balanceBefore = (float) $balance->current_balance;
            $newBalance = $balanceBefore + $amount + $progressionAmount;
            $ceiling = $tranche->ceiling_amount ? (float) $tranche->ceiling_amount : null;
            $balanceAfter = $ceiling ? min($newBalance, $ceiling) : $newBalance;

            $balance->update([
                'current_balance' => $balanceAfter,
                'last_repayment_at' => now(),
                'last_progression_at' => $rule ? now() : $balance->last_progression_at,
            ]);

            CreditEvent::create([
                'ulid' => Str::ulid(), 'facility_id' => $facilityId,
                'tranche_id' => $trancheId, 'event_type' => 'REPAYMENT',
                'amount' => $amount, 'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'notes' => ['days_elapsed' => $daysElapsed],
            ]);

            if ($progressionAmount > 0) {
                CreditEvent::create([
                    'ulid' => Str::ulid(), 'facility_id' => $facilityId,
                    'tranche_id' => $trancheId, 'event_type' => 'PROGRESSION',
                    'amount' => $progressionAmount,
                    'balance_before' => $balanceBefore + $amount,
                    'balance_after' => $balanceAfter,
                    'notes' => ['rule_label' => $rule->label],
                ]);
            }

            $suspensionRule = CreditProgressionRule::where('is_suspension_trigger', true)
                ->where('max_days_to_qualify', '<', $daysElapsed)->exists();

            if ($suspensionRule) {
                $account = FacilityCreditAccount::where('facility_id', $facilityId)->first();
                if ($account) {
                    $account->update([
                        'account_status' => 'SUSPENDED', 'suspended_at' => now(),
                        'suspended_reason' => "Repayment exceeded {$daysElapsed} days.",
                    ]);
                    CreditEvent::create([
                        'ulid' => Str::ulid(), 'facility_id' => $facilityId,
                        'tranche_id' => $trancheId, 'event_type' => 'SUSPENSION',
                        'amount' => 0, 'balance_before' => $balanceAfter,
                        'balance_after' => $balanceAfter,
                        'notes' => ['reason' => 'Late repayment trigger'],
                    ]);
                }
            }

            $this->checkTierUnlocks($facilityId, $trancheId, $balanceAfter, $tranche);

            if ($amount > 0) {
                $this->distributeReturn($trancheId, $amount);
            }
        });
    }

    public function distributeReturn(int $trancheId, float $totalReturn): void
    {
        $parties = CreditTrancheParty::where('tranche_id', $trancheId)
            ->where('is_active', true)->get();

        foreach ($parties as $party) {
            $partyShare = round($totalReturn * ($party->return_percentage / 100), 2);

            CreditEvent::create([
                'ulid' => Str::ulid(), 'facility_id' => 0,
                'tranche_id' => $trancheId, 'event_type' => 'RETURN_DISTRIBUTION',
                'amount' => $partyShare, 'balance_before' => 0, 'balance_after' => 0,
                'notes' => [
                    'party_id' => $party->id, 'party_name' => $party->party_name,
                    'total_return' => $totalReturn,
                ],
            ]);
        }
    }

    private function loadConfig(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $tranches = CreditTranche::with(['activeTiers', 'activeParties'])
                ->where('is_active', true)->orderBy('id')->get();

            return [
                'tranches' => $tranches->map(fn($t) => [
                    'id' => $t->id, 'name' => $t->name,
                    'entry_amount' => (float) $t->entry_amount,
                    'ceiling_amount' => $t->ceiling_amount ? (float) $t->ceiling_amount : null,
                    'is_fixed' => $t->is_fixed,
                    'approval_pathway' => $t->approval_pathway,
                    'product_restriction_scope' => $t->product_restriction_scope,
                    'tiers' => $t->activeTiers->map(fn($tier) => [
                        'id' => $tier->id, 'name' => $tier->name,
                        'product_scope_filter' => $tier->product_scope_filter,
                        'unlock_threshold_pct' => (float) $tier->unlock_threshold_pct,
                        'allocation_pct' => (float) $tier->allocation_pct,
                        'approval_required' => $tier->approval_required,
                    ])->values()->toArray(),
                ])->values()->toArray(),
            ];
        });
    }

    private function productAllowedInTranche(array $item, array $tranche): bool
    {
        $scope = $tranche['product_restriction_scope'];
        if (empty($scope)) return true;

        $skuList = is_array($scope) ? $scope : [];
        return in_array($item['product_id'] ?? null, $skuList)
            || in_array($item['sku_code'] ?? null, $skuList);
    }

    private function resolveTier(array $item, array $tranche, FacilityTrancheBalance $balance): ?int
    {
        $ceiling = $tranche['ceiling_amount'];
        if (! $ceiling) return null;

        $utilisationPct = ((float) $balance->current_balance / $ceiling) * 100;

        foreach ($tranche['tiers'] as $tier) {
            if ($utilisationPct >= $tier['unlock_threshold_pct']) {
                $filter = $tier['product_scope_filter'];
                if (empty($filter)) return $tier['id'];
                if (in_array($item['product_id'] ?? null, $filter)
                    || in_array($item['sku_code'] ?? null, $filter)) {
                    return $tier['id'];
                }
            }
        }

        return null;
    }

    private function checkTierUnlocks(int $facilityId, int $trancheId, float $newBalance, CreditTranche $tranche): void
    {
        $ceiling = $tranche->ceiling_amount ? (float) $tranche->ceiling_amount : null;
        if (! $ceiling) return;

        $utilisationPct = ($newBalance / $ceiling) * 100;

        foreach ($tranche->activeTiers as $tier) {
            if ($utilisationPct >= (float) $tier->unlock_threshold_pct) {
                CreditEvent::create([
                    'ulid' => Str::ulid(), 'facility_id' => $facilityId,
                    'tranche_id' => $trancheId, 'tier_id' => $tier->id,
                    'event_type' => 'TIER_UNLOCK', 'amount' => 0,
                    'balance_before' => $newBalance, 'balance_after' => $newBalance,
                    'notes' => ['tier_name' => $tier->name],
                ]);
            }
        }
    }
}
