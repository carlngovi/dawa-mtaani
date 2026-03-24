<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataRetentionPolicySeeder extends Seeder
{
    /**
     * Seed the data_retention_policies table with default policies.
     */
    public function run(): void
    {
        $now = now();

        $policies = [
            ['data_category' => 'financial_records', 'retention_years' => 7, 'action_on_expiry' => 'ANONYMISE'],
            ['data_category' => 'personal_data', 'retention_years' => 2, 'action_on_expiry' => 'ANONYMISE'],
            ['data_category' => 'audit_logs', 'retention_years' => 7, 'action_on_expiry' => 'ANONYMISE'],
            ['data_category' => 'pos_entries', 'retention_years' => 5, 'action_on_expiry' => 'ANONYMISE'],
            ['data_category' => 'whatsapp_messages', 'retention_years' => 1, 'action_on_expiry' => 'DELETE'],
            ['data_category' => 'security_events', 'retention_years' => 2, 'action_on_expiry' => 'DELETE'],
            ['data_category' => 'session_fingerprints', 'retention_years' => 2, 'action_on_expiry' => 'DELETE'],
        ];

        $rows = array_map(fn ($policy) => array_merge($policy, [
            'is_active' => true,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $policies);

        DB::table('data_retention_policies')->upsert(
            $rows,
            ['data_category'],
            ['retention_years', 'action_on_expiry', 'updated_at']
        );
    }
}
