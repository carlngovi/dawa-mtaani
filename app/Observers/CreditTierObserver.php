<?php

namespace App\Observers;

use App\Models\CreditTier;
use Illuminate\Validation\ValidationException;

/**
 * Enforces business rule: active tier allocation_percentages on a tranche
 * must not exceed 100. Throws a ValidationException if violated.
 */
class CreditTierObserver
{
    public function saving(CreditTier $tier): void
    {
        if (! $tier->is_active) {
            return;
        }

        $existingSum = CreditTier::where('tranche_id', $tier->tranche_id)
            ->where('is_active', true)
            ->when($tier->exists, fn ($q) => $q->where('id', '!=', $tier->id))
            ->sum('allocation_pct');

        $newTotal = $existingSum + $tier->allocation_pct;

        if ($newTotal > 100) {
            throw ValidationException::withMessages([
                'allocation_pct' => "Adding this tier's allocation % ({$tier->allocation_pct}) would bring the tranche total to {$newTotal}%. Maximum is 100%.",
            ]);
        }
    }
}
