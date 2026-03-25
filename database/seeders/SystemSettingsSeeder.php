<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SystemSettingsSeeder
 *
 * Seeds the system_settings table with all defaults from Technical Spec v3.6.
 * Uses upsert so it is safe to re-run — existing values are not overwritten
 * if they have been customised in production.
 *
 * After seeding, update values via the /super/settings panel (super_admin only).
 * Never hardcode any of these values in application code — always read via
 * the CurrencyConfig service or DB::table('system_settings')->where('key', ...).
 */
class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $settings = [

            // ── CURRENCY (CurrencyConfig service reads these) ─────────────
            ['key' => 'currency_iso_code',       'value' => 'KES'],
            ['key' => 'currency_symbol',         'value' => 'KES'],
            ['key' => 'currency_decimal_places', 'value' => '2'],
            ['key' => 'grant_exchange_rate',     'value' => '127'],   // LOCKED for pilot — do not modify
            ['key' => 'grant_base_currency',     'value' => 'USD'],

            // ── TIMEZONE ──────────────────────────────────────────────────
            ['key' => 'display_timezone',        'value' => 'Africa/Nairobi'],

            // ── PPB VERIFICATION (Module 1) ───────────────────────────────
            ['key' => 'ppb_verification_mode',   'value' => 'FILE'],  // FILE = pilot default, API = Phase 2
            ['key' => 'ppb_registry_stale_days', 'value' => '7'],
            ['key' => 'ppb_csv_column_map',      'value' => '{"licence_number":0,"facility_name":1,"ppb_type":2,"licence_status":3,"registered_address":4,"licence_expiry_date":5}'],

            // ── GPS (Module 1) ────────────────────────────────────────────
            ['key' => 'gps_accuracy_threshold_metres', 'value' => '50'],

            // ── DELIVERY & DISPUTES (Module 3) ────────────────────────────
            ['key' => 'confirmation_clock_hours', 'value' => '72'],
            ['key' => 'dispute_sla_hours',         'value' => '48'],
            ['key' => 'sga_mandatory_response_hours', 'value' => '24'],

            // ── CREDIT ENGINE (Module 6) ──────────────────────────────────
            ['key' => 'variance_flag_threshold',  'value' => '15'],
            ['key' => 'mfa_credit_draw_threshold','value' => '50000'], // KES — MFA required above this

            // ── PAYMENT & COPAY (Module 7) ────────────────────────────────
            ['key' => 'copay_escalation_timeout_hours', 'value' => '24'],
            ['key' => 'copay_max_retries_per_24h',      'value' => '5'],
            ['key' => 'copay_max_lifetime_retries',     'value' => '20'],

            // ── LPO (Module 8) ────────────────────────────────────────────
            ['key' => 'lpo_sla_hours',            'value' => '4'],

            // ── PRICE INTELLIGENCE (Module 9) ─────────────────────────────
            ['key' => 'nila_price_deviation_pct', 'value' => '10'],   // Alert + PENDING_REVIEW hold above this

            // ── OFFLINE SYNC (Module 18) ──────────────────────────────────
            ['key' => 'sync_rate_limit_per_minute', 'value' => '1000'],

            // ── ONLINE STORE ELIGIBILITY (Module 15) ─────────────────────
            ['key' => 'min_pos_days',             'value' => '90'],
            ['key' => 'max_variance_score',       'value' => '0.30'],
            ['key' => 'search_cache_minutes',     'value' => '15'],

            // ── ONLINE ORDER & FULFILMENT (Module 16) ────────────────────
            ['key' => 'reservation_minutes',      'value' => '15'],
            ['key' => 'platform_fee_pct',         'value' => '3.00'],
            ['key' => 'partial_fulfilment',       'value' => 'true'],
            ['key' => 'promo_stacking',           'value' => 'false'],
            ['key' => 'b2c_delivery_fee_retained_on_cancel', 'value' => 'true'],

            // ── SECURITY (Module 24) ──────────────────────────────────────
            ['key' => 'session_financial_timeout_minutes', 'value' => '15'],
            ['key' => 'remember_me_max_days',              'value' => '30'],
            ['key' => 'max_concurrent_sessions',           'value' => '3'],
            ['key' => 'mfa_lock_duration_minutes',         'value' => '60'],
            ['key' => 'mfa_max_attempts',                  'value' => '3'],
            ['key' => 'mfa_backup_code_count',             'value' => '10'],
            ['key' => 'mfa_backup_code_low_threshold',     'value' => '3'],

            // ── RATE LIMITS (Module 24 v3.5) ──────────────────────────────
            ['key' => 'rate_limit_public',         'value' => '60'],   // per minute per IP
            ['key' => 'rate_limit_authenticated',  'value' => '300'],  // per minute per user
            ['key' => 'rate_limit_sensitive',       'value' => '10'],   // per hour per user

            // ── MONITORING & ALERTS (Module 28) ──────────────────────────
            ['key' => 'critical_escalation_minutes',   'value' => '15'],
            ['key' => 'warning_escalation_minutes',    'value' => '120'],
            ['key' => 'job_failure_alert_threshold',   'value' => '3'],
            ['key' => 'mpesa_degraded_mode',           'value' => 'false'],
            ['key' => 'mpesa_degraded_threshold_pct',  'value' => '50'],
            ['key' => 'mpesa_recovery_threshold_pct',  'value' => '80'],
            ['key' => 'datanav_escalation_phone',      'value' => ''],
            ['key' => 'datanav_escalation_email',      'value' => ''],
            ['key' => 'primary_admin_phone',           'value' => ''],
            ['key' => 'primary_admin_email',           'value' => ''],

            // ── BACKUP (Module 28 v3.5) ───────────────────────────────────
            ['key' => 'backup_daily_retention_days',   'value' => '30'],
            ['key' => 'backup_weekly_retention_weeks', 'value' => '12'],
            ['key' => 'backup_monthly_retention_months','value' => '12'],
            ['key' => 'backup_financial_min_years',    'value' => '7'],

            // ── DATA RETENTION & DPA (Module 29) ─────────────────────────
            ['key' => 'retention_financial_years',     'value' => '7'],
            ['key' => 'retention_personal_data_years', 'value' => '2'],
            ['key' => 'anonymisation_schedule_cron',   'value' => '0 1 * * *'], // nightly 1am EAT
            ['key' => 'dsar_export_schema_version',    'value' => '1'],

            // ── WEBHOOKS (Module 13 v3.5) ─────────────────────────────────
            ['key' => 'webhook_retry_max_attempts',    'value' => '6'],
            ['key' => 'webhook_idempotency_window_hours','value' => '24'],

            // ── GEOGRAPHIC DATA (Module 1 v3.5) ───────────────────────────
            ['key' => 'iebc_hierarchy_version',        'value' => '2022'],
        ];

        $rows = array_map(fn ($s) => array_merge($s, [
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $settings);

        DB::table('system_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
    }
}
