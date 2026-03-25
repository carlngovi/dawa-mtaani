<?php
namespace App\Observers;

use App\Models\CreditTrancheParty;
use App\Models\CreditConfigChangelog;
use App\Services\CurrencyConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CreditTranchePartyObserver
{
    public function saved(CreditTrancheParty $party): void
    {
        foreach ($party->getChanges() as $field => $newValue) {
            if (in_array($field, ['updated_at', 'created_at'])) continue;

            CreditConfigChangelog::create([
                'changed_by'   => Auth::id() ?? 1,
                'model_type'   => CreditTrancheParty::class,
                'model_id'     => $party->id,
                'field_name'   => $field,
                'value_before' => $party->getOriginal($field),
                'value_after'  => $newValue,
            ]);
        }

        CurrencyConfig::clearCache();
        Cache::forget('credit_engine_config');
    }

    public function deleted(CreditTrancheParty $party): void
    {
        CurrencyConfig::clearCache();
        Cache::forget('credit_engine_config');
    }
}
