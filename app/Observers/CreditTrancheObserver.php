<?php
namespace App\Observers;

use App\Models\CreditTranche;
use App\Models\CreditConfigChangelog;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreditTrancheObserver
{
    public function saving(CreditTranche $tranche): void
    {
        if (! $tranche->exists) {
            return;
        }

        $sum = $tranche->activeParties()->sum('risk_percentage');

        if ($sum > 0 && abs($sum - 100.00) > 0.01) {
            throw ValidationException::withMessages([
                'risk_percentage' => [
                    "Party risk percentages must sum to 100%. Current sum: {$sum}%"
                ],
            ]);
        }
    }

    public function saved(CreditTranche $tranche): void
    {
        foreach ($tranche->getChanges() as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'])) continue;

            CreditConfigChangelog::create([
                'changed_by'   => Auth::id() ?? 1,
                'model_type'   => CreditTranche::class,
                'model_id'     => $tranche->id,
                'field_name'   => $field,
                'value_before' => $tranche->getOriginal($field),
                'value_after'  => $newValue,
            ]);
        }

        CurrencyConfig::clearCache();
        \Illuminate\Support\Facades\Cache::forget('credit_engine_config');
    }

    public function deleted(CreditTranche $tranche): void
    {
        CurrencyConfig::clearCache();
        \Illuminate\Support\Facades\Cache::forget('credit_engine_config');
    }
}
