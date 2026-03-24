<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed RBAC roles and permissions.
     */
    public function run(): void
    {
        // Step 1 — Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Step 2 — Create all permissions
        $permissions = [
            'view-own-orders',
            'place-orders',
            'view-own-credit',
            'submit-disputes',
            'submit-lpo',
            'view-own-pos',
            'submit-quality-flag',
            'view-own-supplier-orders',
            'manage-own-catalogue',
            'trigger-dispatch',
            'view-own-performance',
            'view-assigned-deliveries',
            'mark-delivered',
            'upload-pod',
            'view-group-facilities',
            'view-group-credit',
            'place-orders-as-authorised-placer',
            'view-group-orders',
            'view-all-facilities',
            'update-facility-status',
            'resolve-disputes',
            'approve-lpo',
            'view-flags',
            'view-quality-flags',
            'manage-credit-config',
            'view-audit-log',
            'view-price-intelligence',
            'manage-system-settings',
            'manage-authorised-placers',
            'manage-pharmacy-groups',
            'manage-pricing-agreements',
            'manage-courier-assignments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Step 3 — Create roles and assign permissions
        Role::firstOrCreate(['name' => 'retail_facility'])
            ->syncPermissions([
                'view-own-orders',
                'place-orders',
                'view-own-credit',
                'submit-disputes',
                'submit-lpo',
                'view-own-pos',
                'submit-quality-flag',
            ]);

        Role::firstOrCreate(['name' => 'wholesale_facility'])
            ->syncPermissions([
                'view-own-supplier-orders',
                'manage-own-catalogue',
                'trigger-dispatch',
                'view-own-performance',
            ]);

        Role::firstOrCreate(['name' => 'logistics_facility'])
            ->syncPermissions([
                'view-assigned-deliveries',
                'mark-delivered',
                'upload-pod',
            ]);

        Role::firstOrCreate(['name' => 'group_owner'])
            ->syncPermissions([
                'view-group-facilities',
                'view-group-credit',
                'place-orders-as-authorised-placer',
                'view-group-orders',
            ]);

        Role::firstOrCreate(['name' => 'network_field_agent'])
            ->syncPermissions([
                'view-all-facilities',
                'update-facility-status',
                'resolve-disputes',
                'approve-lpo',
                'view-flags',
                'view-quality-flags',
            ]);

        Role::firstOrCreate(['name' => 'network_admin'])
            ->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'system'])
            ->syncPermissions($permissions);
    }
}
