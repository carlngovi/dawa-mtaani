<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoleAssignmentService
{
    private array $typeToRole = [
        'RETAIL'       => 'retail_facility',
        'WHOLESALE'    => 'wholesale_facility',
        'HOSPITAL'     => 'retail_facility',
        'MANUFACTURER' => 'wholesale_facility',
    ];

    public function assignForFacilityType(User $user, string $ppbFacilityType): void
    {
        $role = $this->typeToRole[$ppbFacilityType] ?? null;

        if (! $role) {
            Log::error('RoleAssignmentService: unknown facility type', [
                'type'    => $ppbFacilityType,
                'user_id' => $user->id,
            ]);
            return;
        }

        try {
            // Remove any existing facility roles first
            $user->removeRole(array_unique(array_values($this->typeToRole)));
            $user->assignRole($role);

            Log::info('RoleAssignmentService: role assigned', [
                'user_id' => $user->id,
                'role'    => $role,
                'type'    => $ppbFacilityType,
            ]);
        } catch (\Throwable $e) {
            Log::error('RoleAssignmentService: failed to assign role', [
                'user_id' => $user->id,
                'role'    => $role,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getRoleForType(string $ppbFacilityType): ?string
    {
        return $this->typeToRole[$ppbFacilityType] ?? null;
    }
}
