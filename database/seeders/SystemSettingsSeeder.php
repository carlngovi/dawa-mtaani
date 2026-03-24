<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Seed the system_settings table with default configuration values.
     */
    public function run(): void
    {
        $now = now();

        $settings = [
            // CURRENCY (Module 6 — CurrencyConfig)
            ['key' => 'currency_iso_code', 'value' => 'KES'],
            ['key' => 'currency_symbol', 'value' => 'KES'],
            ['key' => 'currency_decimal_places', 'value' => '2'],
            ['key' => 'grant_exchange_rate', 'value' => '127'],
            ['key' => 'grant_base_currency', 'value' => 'USD'],

            // TIMEZONE (Core Architecture)
            ['key' => 'display_timezone', 'value' => 'Africa/Nairobi'],

            // PPB VERIFICATION (Module 1)
            ['key' => 'ppb_verification_mode', 'value' => 'FILE'],
            ['key' => 'ppb_registry_stale_days', 'value' => '7'],
            ['key' => 'ppb_csv_column_map', 'value' => '{"licence_number":0,"facility_name":1,"ppb_type":2,"licence_status":3,"registered_address":4,"licence_expiry_date":5}'],

            // DELIVERY & DISPUTES (Module 3)
            ['key' => 'confirmation_clock_hours', 'value' => '72'],
            ['key' => 'dispute_sla_hours', 'value' => '48'],

            // CREDIT ENGINE (Module 6)
            ['key' => 'variance_flag_threshold', 'value' => '15'],

            // PAYMENT & COPAY (Module 7)
            ['key' => 'copay_escalation_timeout_hours', 'value' => '24'],
            ['key' => 'copay_max_retries_per_24h', 'value' => '5'],
            ['key' => 'copay_max_lifetime_retries', 'value' => '20'],

            // LPO (Module 8)
            ['key' => 'lpo_sla_hours', 'value' => '4'],

            // OFFLINE SYNC (Module 18)
            ['key' => 'sync_rate_limit_per_minute', 'value' => '1000'],

            // B2C STORE (Module 16)
            ['key' => 'reservation_window_minutes', 'value' => '15'],
            ['key' => 'platform_fee_pct', 'value' => '5'],

            // MONITORING & ALERTS (Module 28)
            ['key' => 'critical_escalation_minutes', 'value' => '15'],
            ['key' => 'warning_escalation_minutes', 'value' => '120'],
            ['key' => 'mpesa_degraded_mode', 'value' => 'false'],
            ['key' => 'mpesa_degraded_threshold_pct', 'value' => '50'],
            ['key' => 'mpesa_recovery_threshold_pct', 'value' => '80'],
            ['key' => 'datanav_escalation_phone', 'value' => ''],
            ['key' => 'datanav_escalation_email', 'value' => ''],
            ['key' => 'primary_admin_phone', 'value' => ''],
            ['key' => 'primary_admin_email', 'value' => ''],
        ];

        $rows = array_map(fn ($setting) => array_merge($setting, [
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $settings);

        DB::table('system_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
