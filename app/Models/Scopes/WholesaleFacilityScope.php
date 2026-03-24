<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WholesaleFacilityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply when authenticated user is a wholesale_facility role
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Only scope for wholesale_facility role
        // network_admin bypasses this scope for full visibility
        if ($user->hasRole(['network_admin', 'system'])) {
            return;
        }

        if (! $user->hasRole('wholesale_facility')) {
            return;
        }

        $facilityId = $user->facility_id;

        if (! $facilityId) {
            return;
        }

        // Apply scope based on which column the model uses
        $table = $builder->getModel()->getTable();

        $wholesaleColumn = match ($table) {
            'orders'                 => 'wholesale_facility_id',
            'wholesale_price_lists'  => 'wholesale_facility_id',
            'facility_stock_status'  => 'wholesale_facility_id',
            'dispatch_triggers'      => 'triggered_by_facility_id',
            default                  => null,
        };

        if ($wholesaleColumn) {
            $builder->where("{$table}.{$wholesaleColumn}", $facilityId);
        }
    }
}
