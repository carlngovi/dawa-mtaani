<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TestUsersSeeder
 *
 * Creates one test user per role for local development and QA.
 * ALL users share the same password: Password@123
 *
 * NEVER run this seeder in production.
 * Guard: aborts if APP_ENV is production.
 *
 * Usage:
 *   php artisan db:seed --class=TestUsersSeeder
 *
 * Login at: /login
 * Then use the email below for whichever portal you want to test.
 */
class TestUsersSeeder extends Seeder
{
    private const PASSWORD = 'Password@123';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('TestUsersSeeder must not run in production. Aborting.');
            return;
        }

        // ── Create two facilities (retail + wholesale) for facility-role users ──

        $retailFacility = Facility::firstOrCreate(
            ['ppb_licence_number' => 'PPB-TEST-RETAIL-001'],
            [
                'ulid'               => \Str::ulid(),
                'facility_name'      => 'Test Retail Pharmacy',
                'owner_name'         => 'Test Owner',
                'ppb_facility_type'  => 'RETAIL',
                'ppb_licence_status' => 'VALID',
                'phone'              => '+254700000001',
                'email'              => 'retail.facility@test.com',
                'county'             => 'Kilifi',
                'sub_county'         => 'Kilifi North',
                'ward'               => 'Kilifi',
                'physical_address'   => '1 Test Street, Kilifi',
                'network_membership' => 'NETWORK',
                'facility_status'    => 'ACTIVE',
                'onboarding_status'  => 'ACTIVE',
                'created_by'         => 1,
            ]
        );

        $wholesaleFacility = Facility::firstOrCreate(
            ['ppb_licence_number' => 'PPB-TEST-WHOLESALE-001'],
            [
                'ulid'               => \Str::ulid(),
                'facility_name'      => 'Test Wholesale Distributor',
                'owner_name'         => 'Test Wholesale Owner',
                'ppb_facility_type'  => 'WHOLESALE',
                'ppb_licence_status' => 'VALID',
                'phone'              => '+254700000002',
                'email'              => 'wholesale.facility@test.com',
                'county'             => 'Nairobi',
                'sub_county'         => 'Westlands',
                'ward'               => 'Parklands',
                'physical_address'   => '2 Distribution Road, Nairobi',
                'network_membership' => 'NETWORK',
                'facility_status'    => 'ACTIVE',
                'onboarding_status'  => 'ACTIVE',
                'created_by'         => 1,
            ]
        );

        $logisticsFacility = Facility::firstOrCreate(
            ['ppb_licence_number' => 'PPB-TEST-LOGISTICS-001'],
            [
                'ulid'               => \Str::ulid(),
                'facility_name'      => 'SGA Test Courier',
                'owner_name'         => 'SGA Test',
                'ppb_facility_type'  => 'WHOLESALE',  // SGA courier — distinguished by role, not PPB type
                'ppb_licence_status' => 'VALID',
                'phone'              => '+254700000003',
                'email'              => 'logistics.facility@test.com',
                'county'             => 'Nairobi',
                'sub_county'         => 'Westlands',
                'ward'               => 'Parklands',
                'physical_address'   => '3 Courier Lane, Nairobi',
                'network_membership' => 'NETWORK',
                'facility_status'    => 'ACTIVE',
                'onboarding_status'  => 'ACTIVE',
                'created_by'         => 1,
            ]
        );

        // ── User definitions ──────────────────────────────────────────────
        // Each entry: [email, name, role, facility_id, landing_portal]

        $users = [

            // ── Admin tiers ───────────────────────────────────────────────
            [
                'email'       => 'technical.admin@test.com',
                'name'        => 'Technical Admin (T0)',
                'role'        => 'technical_admin',
                'facility_id' => null,
                'portal'      => '/tech/diagnostics',
            ],
            [
                'email'       => 'super.admin@test.com',
                'name'        => 'Super Admin (T1)',
                'role'        => 'super_admin',
                'facility_id' => null,
                'portal'      => '/super/settings',
            ],
            [
                'email'       => 'admin@test.com',
                'name'        => 'Admin (T2)',
                'role'        => 'admin',
                'facility_id' => null,
                'portal'      => '/admin/dashboard',
            ],
            [
                'email'       => 'network.admin@test.com',
                'name'        => 'Network Admin (legacy)',
                'role'        => 'network_admin',
                'facility_id' => null,
                'portal'      => '/admin/dashboard',
            ],
            [
                'email'       => 'assistant.admin@test.com',
                'name'        => 'Assistant Admin (T3)',
                'role'        => 'assistant_admin',
                'facility_id' => null,
                'portal'      => '/assistant/dashboard',
            ],
            [
                'email'       => 'admin.support@test.com',
                'name'        => 'Admin Support (T4)',
                'role'        => 'admin_support',
                'facility_id' => null,
                'portal'      => '/support/tickets',
            ],

            // ── Finance ───────────────────────────────────────────────────
            [
                'email'       => 'shared.accountant@test.com',
                'name'        => 'Shared Accountant',
                'role'        => 'shared_accountant',
                'facility_id' => null,
                'portal'      => '/finance/settlement',
            ],

            // ── Facility roles ────────────────────────────────────────────
            [
                'email'       => 'retail.facility@test.com',
                'name'        => 'Retail Pharmacy User',
                'role'        => 'retail_facility',
                'facility_id' => $retailFacility->id,
                'portal'      => '/retail/dashboard',
            ],
            [
                'email'       => 'wholesale.facility@test.com',
                'name'        => 'Wholesale Facility User',
                'role'        => 'wholesale_facility',
                'facility_id' => $wholesaleFacility->id,
                'portal'      => '/wholesale/orders',
            ],
            [
                'email'       => 'logistics.facility@test.com',
                'name'        => 'Logistics Facility User (SGA)',
                'role'        => 'logistics_facility',
                'facility_id' => $logisticsFacility->id,
                'portal'      => '/logistics/deliveries',
            ],
            [
                'email'       => 'group.owner@test.com',
                'name'        => 'Group Owner',
                'role'        => 'group_owner',
                'facility_id' => $retailFacility->id,
                'portal'      => '/group/dashboard',
            ],

            // ── Field roles ───────────────────────────────────────────────
            [
                'email'       => 'field.agent@test.com',
                'name'        => 'Network Field Agent',
                'role'        => 'network_field_agent',
                'facility_id' => null,
                'portal'      => '/field/pharmacies',
            ],
            [
                'email'       => 'sales.rep@test.com',
                'name'        => 'Sales Rep',
                'role'        => 'sales_rep',
                'facility_id' => null,
                'portal'      => '/rep/pharmacies',
            ],

            // ── External ──────────────────────────────────────────────────
            [
                'email'       => 'customer@test.com',
                'name'        => 'Customer (B2C)',
                'role'        => 'customer',
                'facility_id' => null,
                'portal'      => '/store',
            ],
        ];

        // ── Create users ──────────────────────────────────────────────────

        $this->command->info('');
        $this->command->info('Creating test users...');
        $this->command->info('');

        $rows = [];

        foreach ($users as $def) {
            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name'        => $def['name'],
                    'password'    => Hash::make(self::PASSWORD),
                    'facility_id' => $def['facility_id'],
                    'is_active'   => true,
                ]
            );

            $user->syncRoles([$def['role']]);

            $rows[] = [
                $def['role'],
                $def['email'],
                $def['portal'],
            ];
        }

        // ── Print credentials table ───────────────────────────────────────

        $this->command->table(
            ['Role', 'Email', 'Landing Portal'],
            $rows
        );

        $this->command->info('');
        $this->command->info('Password for all users: ' . self::PASSWORD);
        $this->command->info('Login at: /login');
        $this->command->info('');
        $this->command->warn('NEVER run this seeder in production.');
        $this->command->info('');
    }
}
