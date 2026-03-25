<?php

namespace App\Observers;

use App\Models\CreditTrancheParty;
use Illuminate\Validation\ValidationException;

/**
 * Enforces business rule: active party risk_percentages on a tranche must sum to 100.
 * Throws a ValidationException if violated — caught by the controller and returned
 * as a user-facing error.
 */
class CreditTranchePartyObserver
{
    public function saving(CreditTrancheParty $party): void
    {
        if (! $party->is_active) {
            return; // Inactive parties excluded from the sum check
        }

        $existingSum = CreditTrancheParty::where('tranche_id', $party->tranche_id)
            ->where('is_active', true)
            ->when($party->exists, fn ($q) => $q->where('id', '!=', $party->id))
            ->sum('risk_percentage');

        $newTotal = $existingSum + $party->risk_percentage;

        if ($newTotal > 100) {
            throw ValidationException::withMessages([
                'risk_percentage' => "Adding this party's risk % ({$party->risk_percentage}) would bring the tranche total to {$newTotal}%. Maximum is 100%.",
            ]);
        }
    }
}
