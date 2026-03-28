<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Models\SpotterProfile;

trait HasSpotterScope
{
    protected function spotterScope(): array
    {
        $user = auth()->user();

        return [
            'isSalesRep' => $user->hasRole('sales_rep'),
            'isCC' => $user->hasRole('county_coordinator'),
            'isAdmin' => $user->hasAnyRole(['admin', 'super_admin', 'technical_admin', 'assistant_admin', 'network_admin']),
            'spotterIds' => $user->hasRole('sales_rep')
                ? SpotterProfile::where('sales_rep_user_id', $user->id)->pluck('user_id')
                : collect(),
            'county' => $user->hasRole('county_coordinator')
                ? SpotterProfile::where('user_id', $user->id)->value('county')
                : null,
        ];
    }

    protected function applySubmissionScope($query, array $scope): mixed
    {
        if ($scope['isSalesRep'] && $scope['spotterIds']->isNotEmpty()) {
            return $query->whereIn('spotter_user_id', $scope['spotterIds']);
        }
        if ($scope['isCC'] && $scope['county']) {
            return $query->where('county', $scope['county']);
        }

        return $query;
    }

    protected function roleLabel(array $scope): string
    {
        if ($scope['isSalesRep']) {
            return 'Sales Rep Dashboard';
        }
        if ($scope['isCC']) {
            return 'County Coordinator Dashboard';
        }

        return 'Admin Dashboard';
    }
}
