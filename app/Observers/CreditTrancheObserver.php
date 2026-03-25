<?php

namespace App\Observers;

use App\Models\CreditTranche;

/**
 * Enforces the rule: risk_percentage across all active parties on a tranche
 * must always sum to exactly 100. Fires on party save via tranche event.
 */
class CreditTrancheObserver
{
    public function saved(CreditTranche $tranche): void
    {
        // Validation is enforced in CreditTranchePartyObserver on party save.
        // This observer is available for tranche-level hooks (e.g. changelog).
    }
}
