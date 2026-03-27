<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-own-orders', 'place-orders', 'view-own-credit',
            'submit-disputes', 'submit-lpo', 'view-own-pos', 'submit-quality-flag',
            'view-own-supplier-orders', 'manage-own-catalogue',
            'trigger-dispatch', 'view-own-performance',
            'view-assigned-deliveries', 'mark-delivered', 'upload-pod',
            'view-group-facilities', 'view-group-credit',
            'place-orders-as-authorised-placer', 'view-group-orders',
            'view-all-facilities', 'update-facility-status', 'resolve-disputes',
            'approve-lpo', 'view-flags', 'view-quality-flags',
            'view-financial-data', 'manage-credit-config',
            'view-audit-log', 'view-price-intelligence',
            'manage-system-settings', 'manage-roles',
            'manage-authorised-placers', 'manage-pharmacy-groups',
            'manage-pricing-agreements', 'manage-courier-assignments',
            'read-all', 'gated-write',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'retail_facility', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-own-orders', 'place-orders', 'view-own-credit',
                'submit-disputes', 'submit-lpo', 'view-own-pos', 'submit-quality-flag',
            ]);

        Role::firstOrCreate(['name' => 'wholesale_facility', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-own-supplier-orders', 'manage-own-catalogue',
                'trigger-dispatch', 'view-own-performance',
            ]);

        Role::firstOrCreate(['name' => 'logistics_facility', 'guard_name' => 'web'])
            ->syncPermissions(['view-assigned-deliveries', 'mark-delivered', 'upload-pod']);

        Role::firstOrCreate(['name' => 'group_owner', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-group-facilities', 'view-group-credit',
                'place-orders-as-authorised-placer', 'view-group-orders',
            ]);

        Role::firstOrCreate(['name' => 'network_field_agent', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-all-facilities', 'update-facility-status',
                'resolve-disputes', 'approve-lpo', 'view-flags', 'view-quality-flags',
            ]);

        Role::firstOrCreate(['name' => 'sales_rep', 'guard_name' => 'web'])
            ->syncPermissions(['view-all-facilities']);

        Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web'])
            ->syncPermissions([]);

        Role::firstOrCreate(['name' => 'admin_support', 'guard_name' => 'web'])
            ->syncPermissions(['view-all-facilities', 'view-own-orders', 'view-flags']);

        Role::firstOrCreate(['name' => 'assistant_admin', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-all-facilities', 'update-facility-status',
                'resolve-disputes', 'approve-lpo', 'view-flags', 'view-quality-flags',
                'manage-authorised-placers',
            ]);

        Role::firstOrCreate(['name' => 'shared_accountant', 'guard_name' => 'web'])
            ->syncPermissions(['view-financial-data']);

        $tier2 = [
            'view-own-orders', 'place-orders', 'view-own-credit',
            'submit-disputes', 'resolve-disputes', 'approve-lpo',
            'view-all-facilities', 'update-facility-status',
            'view-flags', 'view-quality-flags',
            'view-financial-data', 'manage-credit-config',
            'view-audit-log', 'view-price-intelligence',
            'manage-authorised-placers', 'manage-pharmacy-groups',
            'manage-pricing-agreements', 'manage-courier-assignments',
        ];

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])
            ->syncPermissions($tier2);

        Role::firstOrCreate(['name' => 'network_admin', 'guard_name' => 'web'])
            ->syncPermissions($tier2);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])
            ->syncPermissions(array_merge($tier2, ['manage-system-settings', 'manage-roles']));

        Role::firstOrCreate(['name' => 'technical_admin', 'guard_name' => 'web'])
            ->syncPermissions(['read-all', 'gated-write']);

        Role::firstOrCreate(['name' => 'system', 'guard_name' => 'web'])
            ->syncPermissions($permissions);

        // Spotter module — county coordinator supervises field agents in a county
        Role::firstOrCreate(['name' => 'county_coordinator', 'guard_name' => 'web'])
            ->syncPermissions([
                'view-all-facilities', 'update-facility-status',
                'resolve-disputes', 'view-flags', 'view-quality-flags',
            ]);
    }
}
