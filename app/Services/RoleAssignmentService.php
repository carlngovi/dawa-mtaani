<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoleAssignmentService
{
    private array $typeToRole = [
        'RETAIL'       => 'retail_facility',
        'HOSPITAL'     => 'retail_facility',   // HOSPITAL treated as RETAIL per spec
        'WHOLESALE'    => 'wholesale_facility',
        'MANUFACTURER' => 'wholesale_facility',
        'LOGISTICS'    => 'logistics_facility', // SGA Courier facility type
    ];

    public function assignFacilityRole(Facility $facility): void
    {
        $role = $this->typeToRole[$facility->ppb_facility_type] ?? null;

        if (! $role) {
            throw new \RuntimeException(
                "Unknown PPB facility type: {$facility->ppb_facility_type}"
            );
        }

        // Assign to the facility's primary user
        // group_owner, field roles, and admin roles are assigned manually
        // by super_admin — never auto-assigned from PPB type
        $user = $facility->users()->first();

        if ($user) {
            $user->syncRoles([$role]);
        }
    }

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
