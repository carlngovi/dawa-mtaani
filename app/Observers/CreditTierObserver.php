<?php
namespace App\Observers;

use App\Models\CreditTier;
use App\Models\CreditConfigChangelog;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CreditTierObserver
{
    public function saving(CreditTier $tier): void
    {
        $query = CreditTier::where('tranche_id', $tier->tranche_id)
            ->where('is_active', true);

        if ($tier->exists) {
            $query->where('id', '!=', $tier->id);
        }

        $otherSum = $query->sum('allocation_pct');
        $newAllocation = $tier->is_active ? (float) $tier->allocation_pct : 0;
        $total = $otherSum + $newAllocation;

        if ($total > 100.01) {
            throw ValidationException::withMessages([
                'allocation_pct' => [
                    "Tier allocation percentages must sum to 100%. Current sum would be: {$total}%"
                ],
            ]);
        }
    }

    public function saved(CreditTier $tier): void
    {
        foreach ($tier->getChanges() as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'])) continue;

            CreditConfigChangelog::create([
                'changed_by'   => Auth::id() ?? 1,
                'model_type'   => CreditTier::class,
                'model_id'     => $tier->id,
                'field_name'   => $field,
                'value_before' => $tier->getOriginal($field),
                'value_after'  => $newValue,
            ]);
        }

        CurrencyConfig::clearCache();
        Cache::forget('credit_engine_config');
    }

    public function deleted(CreditTier $tier): void
    {
        CurrencyConfig::clearCache();
        Cache::forget('credit_engine_config');
    }
}
