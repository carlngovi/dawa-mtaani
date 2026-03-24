<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsAppTemplateSeeder extends Seeder
{
    /**
     * Seed the whatsapp_templates table with default notification templates.
     */
    public function run(): void
    {
        $now = now();

        $templates = [
            [
                'template_name' => 'ORDER_CONFIRMATION',
                'category'      => 'ORDER_CONFIRMATION',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['order_reference', 'total', 'payment_type']),
                'created_at'    => $now,
            ],
            [
                'template_name' => 'DELIVERY_UPDATE',
                'category'      => 'DELIVERY_UPDATE',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['order_reference', 'status']),
                'created_at'    => $now,
            ],
            [
                'template_name' => 'PAYMENT_REMINDER',
                'category'      => 'PAYMENT_REMINDER',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['amount_due', 'due_date', 'days_overdue']),
                'created_at'    => $now,
            ],
            [
                'template_name' => 'CREDIT_ALERT',
                'category'      => 'CREDIT_ALERT',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['tranche_name', 'balance', 'utilisation_pct']),
                'created_at'    => $now,
            ],
            [
                'template_name' => 'WELCOME_ONBOARDED',
                'category'      => 'WELCOME_ONBOARDED',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['facility_name']),
                'created_at'    => $now,
            ],
            [
                'template_name' => 'COPAY_FAILED',
                'category'      => 'COPAY_FAILED',
                'language_code' => 'en',
                'is_active'     => true,
                'variables'     => json_encode(['order_reference', 'failure_reason']),
                'created_at'    => $now,
            ],
        ];

        DB::table('whatsapp_templates')->upsert(
            $templates,
            ['template_name'],
            ['category', 'language_code', 'is_active', 'variables']
        );
    }
}
