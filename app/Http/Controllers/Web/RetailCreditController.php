<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CreditEvent;
use App\Models\CreditProgressionRule;
use App\Models\FacilityCreditAccount;
use App\Models\FacilityTrancheBalance;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetailCreditController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $facility = DB::table('facilities')->where('id', $user->facility_id)->first();

        if (! $facility || $facility->network_membership === 'OFF_NETWORK') {
            abort(404);
        }

        $currency = CurrencyConfig::get();
        $account = FacilityCreditAccount::where('facility_id', $user->facility_id)->first();

        $balances = $account
            ? FacilityTrancheBalance::with(['tranche.activeTiers'])
                ->where('credit_account_id', $account->id)->get()
            : collect();

        $trancheCards = $balances->map(function ($balance) use ($currency) {
            $tranche = $balance->tranche;
            if (! $tranche || ! $tranche->is_active) return null;

            $ceiling = $tranche->ceiling_amount ? (float) $tranche->ceiling_amount : null;
            $current = (float) $balance->current_balance;
            $progressPct = $ceiling && $ceiling > 0 ? min(100, round(($current / $ceiling) * 100, 1)) : null;

            $tierStatus = $tranche->activeTiers->map(function ($tier) use ($current, $ceiling) {
                $unlockAt = $ceiling ? round($ceiling * ($tier->unlock_threshold_pct / 100), 2) : null;
                $isUnlocked = $unlockAt !== null && $current >= $unlockAt;
                $available = $isUnlocked ? round($current * ($tier->allocation_pct / 100), 2) : 0;
                return [
                    'id' => $tier->id, 'name' => $tier->name,
                    'product_scope' => $tier->product_scope_description,
                    'unlock_threshold_pct' => $tier->unlock_threshold_pct,
                    'unlock_at_amount' => $unlockAt, 'is_unlocked' => $isUnlocked,
                    'available' => $available, 'approval_required' => $tier->approval_required,
                ];
            })->values()->toArray();

            return [
                'tranche_id' => $tranche->id, 'name' => $tranche->name,
                'current_balance' => $current, 'entry_balance' => (float) $balance->entry_balance,
                'ceiling' => $ceiling, 'progress_pct' => $progressPct,
                'is_fixed' => $tranche->is_fixed, 'approval_pathway' => $tranche->approval_pathway,
                'last_repayment_at' => $balance->last_repayment_at, 'tiers' => $tierStatus,
            ];
        })->filter()->values();

        $recentEvents = $account
            ? CreditEvent::where('credit_account_id', $account->id)
                ->orderByDesc('occurred_at')->limit(20)->get()
            : collect();

        $healthStatus = 'ON_TRACK';
        $healthMessage = 'Your credit is in good standing.';

        if (! $account) {
            $healthStatus = 'SUSPENDED';
            $healthMessage = 'No credit account found. Contact your network administrator.';
        } elseif ($account->account_status === 'SUSPENDED') {
            $healthStatus = 'SUSPENDED';
            $healthMessage = 'Your credit account is suspended. ' . ($account->suspended_reason ?? 'Contact your network administrator.');
        } elseif ($account->account_status === 'PENDING_ASSESSMENT') {
            $healthStatus = 'AT_RISK';
            $healthMessage = 'Your credit account is pending assessment.';
        } elseif ($trancheCards->isNotEmpty()) {
            $atRisk = $trancheCards->first(fn($t) => $t['progress_pct'] !== null && $t['progress_pct'] >= 85);
            if ($atRisk) {
                $healthStatus = 'AT_RISK';
                $healthMessage = 'Approaching credit limit on ' . $atRisk['name'] . '. Consider repaying soon.';
            }
        }

        $progressionRules = CreditProgressionRule::where('is_suspension_trigger', false)
            ->orderBy('max_days_to_qualify')->get();

        return view('retail.credit', compact(
            'currency', 'account', 'trancheCards', 'recentEvents',
            'healthStatus', 'healthMessage', 'progressionRules', 'facility'
        ));
    }
}
