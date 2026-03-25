<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ProductsSeeder
 *
 * Seeds the 49-SKU pilot product catalogue for the three-county pilot:
 * Kilifi (Coast) + Migori (Lake Victoria) + Kisii (Western Highlands).
 *
 * Source: Expanded Product List v2 — Next Door Ltd internal document.
 * SKUs marked NEW★ are additions beyond the original 30.
 *
 * Uses upsert on sku_code — safe to re-run.
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $products = [
            // ── Anti-Malarials ─────────────────────────────────────────────
            ['sku_code' => 'MAL-001', 'generic_name' => 'Artemether/Lumefantrine 20/120mg Adult',         'therapeutic_category' => 'Anti-Malarials',       'unit_size' => '24 tab pack',          'description' => 'First-line ACT treatment. Endemic in Kilifi + Migori (lakeside).'],
            ['sku_code' => 'MAL-002', 'generic_name' => 'Artemether/Lumefantrine 20/120mg Paediatric',   'therapeutic_category' => 'Anti-Malarials',       'unit_size' => '6 tab dispersible',    'description' => 'Child malaria. Critical for infant mortality target.'],
            ['sku_code' => 'MAL-003', 'generic_name' => 'Sulfadoxine-Pyrimethamine',                     'therapeutic_category' => 'Anti-Malarials',       'unit_size' => '3 tab (IPTp)',         'description' => 'Pregnancy prophylaxis in malaria zones.'],
            ['sku_code' => 'MAL-004', 'generic_name' => 'Quinine 300mg',                                 'therapeutic_category' => 'Anti-Malarials',       'unit_size' => '100 tabs',             'description' => 'Backup when AL unavailable. First trimester pregnancy.'],
            ['sku_code' => 'MAL-005', 'generic_name' => 'Dihydroartemisinin-Piperaquine',                'therapeutic_category' => 'Anti-Malarials',       'unit_size' => '3 tab adult course',   'description' => 'NEW: Alternative ACT when AL fails. WHO-recommended second-line.'],

            // ── Antibiotics Access ─────────────────────────────────────────
            ['sku_code' => 'ANT-ACC-001', 'generic_name' => 'Amoxicillin 250mg Capsules',                'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100 caps',             'description' => 'First-line respiratory/ENT. Universal demand.'],
            ['sku_code' => 'ANT-ACC-002', 'generic_name' => 'Amoxicillin 500mg Capsules',                'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100 caps',             'description' => 'Adult dose respiratory infections.'],
            ['sku_code' => 'ANT-ACC-003', 'generic_name' => 'Amoxicillin 125mg/5ml Suspension',          'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100ml bottle',         'description' => 'Paediatric respiratory infections.'],
            ['sku_code' => 'ANT-ACC-004', 'generic_name' => 'Amoxicillin 250mg/5ml Suspension',          'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100ml bottle',         'description' => 'Older children respiratory infections.'],
            ['sku_code' => 'ANT-ACC-005', 'generic_name' => 'Metronidazole 400mg Tablets',               'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100 tabs',             'description' => 'Anaerobic infections, dental, GI. Universal fast seller.'],
            ['sku_code' => 'ANT-ACC-006', 'generic_name' => 'Cefalexin 250mg Capsules',                  'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100 caps',             'description' => 'Skin/soft tissue infections. UTI backup.'],
            ['sku_code' => 'ANT-ACC-007', 'generic_name' => 'Doxycycline 100mg Capsules',                'therapeutic_category' => 'Antibiotics - Access', 'unit_size' => '100 caps',             'description' => 'NEW: STI treatment. Critical for Migori HIV corridor. Respiratory, acne.'],

            // ── Antibiotics Watch ──────────────────────────────────────────
            ['sku_code' => 'ANT-WCH-001', 'generic_name' => 'Azithromycin 500mg Tablets',               'therapeutic_category' => 'Antibiotics - Watch',  'unit_size' => '3 tab pack',           'description' => 'Fast seller. Respiratory, STIs. Strict stewardship protocol.'],
            ['sku_code' => 'ANT-WCH-002', 'generic_name' => 'Cotrimoxazole 960mg Tablets',               'therapeutic_category' => 'Antibiotics - Watch',  'unit_size' => '100 tabs',             'description' => 'HIV prophylaxis in Migori. UTI treatment. Pneumocystis prevention.'],

            // ── Reproductive Health ────────────────────────────────────────
            ['sku_code' => 'REP-001', 'generic_name' => 'Levonorgestrel 1.5mg (Emergency Contraception)', 'therapeutic_category' => 'Reproductive Health',  'unit_size' => '1 tab',                'description' => 'Emergency contraception. High urban demand in Kisii.'],
            ['sku_code' => 'REP-002', 'generic_name' => 'Combined Oral Contraceptive (Ethinylestradiol/Levonorgestrel)', 'therapeutic_category' => 'Reproductive Health', 'unit_size' => '28 tab cycle', 'description' => 'Steady refill demand.'],
            ['sku_code' => 'REP-003', 'generic_name' => 'Progestogen-Only Pill (Levonorgestrel 30mcg)',  'therapeutic_category' => 'Reproductive Health',  'unit_size' => '28 tab cycle',         'description' => 'Breastfeeding-compatible contraceptive.'],
            ['sku_code' => 'REP-004', 'generic_name' => 'DMPA-SC (Sayana Press)',                        'therapeutic_category' => 'Reproductive Health',  'unit_size' => '1 unit',               'description' => 'Self-injectable. 3-month contraception.'],
            ['sku_code' => 'REP-005', 'generic_name' => 'Misoprostol 200mcg Tablets',                   'therapeutic_category' => 'Reproductive Health',  'unit_size' => '4 tabs',               'description' => 'NEW: Postpartum haemorrhage prevention. WHO essential medicine.'],

            // ── Barrier Contraception ──────────────────────────────────────
            ['sku_code' => 'BAR-001', 'generic_name' => 'Male Condoms',                                  'therapeutic_category' => 'Barrier Contraception','unit_size' => 'Pack of 3',            'description' => 'NEW: HIV prevention essential for Migori. Family planning.'],

            // ── Chronic Disease ────────────────────────────────────────────
            ['sku_code' => 'CHR-001', 'generic_name' => 'Amlodipine 5mg Tablets',                       'therapeutic_category' => 'Chronic Disease',      'unit_size' => '30 tabs',              'description' => 'Hypertension first-line. Higher demand in Kisii.'],
            ['sku_code' => 'CHR-002', 'generic_name' => 'Enalapril 5mg Tablets',                         'therapeutic_category' => 'Chronic Disease',      'unit_size' => '30 tabs',              'description' => 'ACE inhibitor. Heart failure. Diabetic nephropathy.'],
            ['sku_code' => 'CHR-003', 'generic_name' => 'Metformin 500mg Tablets',                       'therapeutic_category' => 'Chronic Disease',      'unit_size' => '100 tabs',             'description' => 'Diabetes first-line. Growing NCD burden.'],
            ['sku_code' => 'CHR-004', 'generic_name' => 'Metformin 850mg Tablets',                       'therapeutic_category' => 'Chronic Disease',      'unit_size' => '100 tabs',             'description' => 'NEW: Higher dose formulation. Kisii has higher NCD load.'],
            ['sku_code' => 'CHR-005', 'generic_name' => 'Glibenclamide 5mg Tablets',                     'therapeutic_category' => 'Chronic Disease',      'unit_size' => '100 tabs',             'description' => 'Sulfonylurea add-on to metformin.'],
            ['sku_code' => 'CHR-006', 'generic_name' => 'Hydrochlorothiazide 25mg Tablets',              'therapeutic_category' => 'Chronic Disease',      'unit_size' => '100 tabs',             'description' => 'NEW: Most common add-on to amlodipine/enalapril. KEML Level 2.'],

            // ── Paediatric Health ──────────────────────────────────────────
            ['sku_code' => 'PED-001', 'generic_name' => 'Oral Rehydration Salts (ORS) Sachets',          'therapeutic_category' => 'Paediatric Health',    'unit_size' => '10 sachets',           'description' => 'WHO standard for childhood diarrhoea. Universal.'],
            ['sku_code' => 'PED-002', 'generic_name' => 'Zinc 20mg Dispersible Tablets',                 'therapeutic_category' => 'Paediatric Health',    'unit_size' => '10 tabs',              'description' => 'Completes ORS treatment. Reduces diarrhoea duration.'],
            ['sku_code' => 'PED-003', 'generic_name' => 'Paracetamol 120mg/5ml Suspension',              'therapeutic_category' => 'Paediatric Health',    'unit_size' => '100ml bottle',         'description' => 'Paediatric fever/pain. Highest-volume paediatric product.'],

            // ── Vitamins & Minerals ────────────────────────────────────────
            ['sku_code' => 'VIT-001', 'generic_name' => 'Ascorbic Acid 500mg Tablets',                   'therapeutic_category' => 'Vitamins & Minerals',  'unit_size' => '100 tabs',             'description' => 'General health. Immune support. Steady demand.'],
            ['sku_code' => 'VIT-002', 'generic_name' => 'Iron + Folic Acid Tablets',                     'therapeutic_category' => 'Vitamins & Minerals',  'unit_size' => '100 tabs',             'description' => 'Maternal health. Anaemia treatment. ANC essential.'],
            ['sku_code' => 'VIT-003', 'generic_name' => 'Vitamin A 200,000 IU Capsules',                 'therapeutic_category' => 'Vitamins & Minerals',  'unit_size' => '100 caps',             'description' => 'Child supplementation. Measles treatment adjunct.'],

            // ── Pain & Allergy ─────────────────────────────────────────────
            ['sku_code' => 'PAI-001', 'generic_name' => 'Ibuprofen 200mg Tablets',                       'therapeutic_category' => 'Pain & Allergy',       'unit_size' => '100 tabs',             'description' => 'Pain/fever. Traffic driver. Universal demand.'],
            ['sku_code' => 'PAI-002', 'generic_name' => 'Cetirizine 10mg Tablets',                       'therapeutic_category' => 'Pain & Allergy',       'unit_size' => '100 tabs',             'description' => 'Antihistamine. Allergic rhinitis. Kisii highlands.'],
            ['sku_code' => 'PAI-003', 'generic_name' => 'Paracetamol 500mg Tablets',                     'therapeutic_category' => 'Pain & Allergy',       'unit_size' => '100 tabs',             'description' => 'NEW: Single highest-volume pharmacy product in Kenya.'],
            ['sku_code' => 'PAI-004', 'generic_name' => 'Diclofenac 50mg Tablets',                       'therapeutic_category' => 'Pain & Allergy',       'unit_size' => '100 tabs',             'description' => 'NEW: Strong NSAID for musculoskeletal pain. Very high demand.'],

            // ── Gastrointestinal ───────────────────────────────────────────
            ['sku_code' => 'GAS-001', 'generic_name' => 'Omeprazole 20mg Capsules',                      'therapeutic_category' => 'Gastrointestinal',     'unit_size' => '100 caps',             'description' => 'NEW: Top pharmacy seller. Peptic ulcer/GERD. Traffic driver.'],
            ['sku_code' => 'GAS-002', 'generic_name' => 'Loperamide 2mg Capsules',                       'therapeutic_category' => 'Gastrointestinal',     'unit_size' => '100 caps',             'description' => 'NEW: Adult acute diarrhoea. Complements paediatric ORS/Zinc.'],
            ['sku_code' => 'GAS-003', 'generic_name' => 'Oral Rehydration Salts — Adult',                'therapeutic_category' => 'Gastrointestinal',     'unit_size' => 'Sachets x10',          'description' => 'NEW: Adult rehydration. Cholera-prone lakeside Migori.'],

            // ── Deworming ──────────────────────────────────────────────────
            ['sku_code' => 'DEW-001', 'generic_name' => 'Albendazole 400mg Tablets',                     'therapeutic_category' => 'Deworming',            'unit_size' => '1 tab',                'description' => 'NEW: National deworming programme. Schools, ANC. One-dose. Universal.'],

            // ── HIV Support ────────────────────────────────────────────────
            ['sku_code' => 'HIV-001', 'generic_name' => 'Cotrimoxazole 240mg/5ml Suspension',            'therapeutic_category' => 'HIV Support',          'unit_size' => '100ml bottle',         'description' => 'NEW: Paediatric HIV prophylaxis. Critical in Migori.'],
            ['sku_code' => 'HIV-002', 'generic_name' => 'Fluconazole 150mg Capsules',                    'therapeutic_category' => 'HIV Support',          'unit_size' => '1 cap',                'description' => 'NEW: Oral/vaginal candidiasis. Opportunistic infections in HIV patients.'],

            // ── Respiratory ────────────────────────────────────────────────
            ['sku_code' => 'RES-001', 'generic_name' => 'Salbutamol 100mcg Inhaler',                     'therapeutic_category' => 'Respiratory',          'unit_size' => '200 dose MDI',         'description' => 'NEW: Asthma reliever. Kisii highlands = more respiratory issues.'],
            ['sku_code' => 'RES-002', 'generic_name' => 'Cough Syrup (Simple Linctus)',                  'therapeutic_category' => 'Respiratory',          'unit_size' => '100ml bottle',         'description' => 'NEW: Symptomatic relief. Extremely high customer demand. Traffic driver.'],

            // ── Antifungal ─────────────────────────────────────────────────
            ['sku_code' => 'FUN-001', 'generic_name' => 'Clotrimazole Vaginal 500mg',                    'therapeutic_category' => 'Antifungal',           'unit_size' => '1 tab + applicator',   'description' => 'Women\'s health. Common condition. Single-dose.'],
            ['sku_code' => 'FUN-002', 'generic_name' => 'Clotrimazole Cream 1%',                         'therapeutic_category' => 'Antifungal',           'unit_size' => '20g tube',             'description' => 'NEW: Topical fungal infections (ringworm, athlete\'s foot). Very common.'],

            // ── Topical/Wound Care ─────────────────────────────────────────
            ['sku_code' => 'TOP-001', 'generic_name' => 'Povidone-Iodine 10% Solution',                  'therapeutic_category' => 'Topical/Wound Care',   'unit_size' => '100ml bottle',         'description' => 'NEW: Wound care. Basic antiseptic. Every pharmacy needs this.'],

            // ── Diagnostics ────────────────────────────────────────────────
            ['sku_code' => 'DIA-001', 'generic_name' => 'Pregnancy Test Strips',                         'therapeutic_category' => 'Diagnostics',          'unit_size' => '50 strips',            'description' => 'Reproductive health package. Counselling entry point.'],
            ['sku_code' => 'DIA-002', 'generic_name' => 'Malaria Rapid Diagnostic Test (mRDT)',           'therapeutic_category' => 'Diagnostics',          'unit_size' => '25 test kit',          'description' => 'NEW: Test before treat. Supports AL stewardship. Critical for Kilifi + Migori.'],
        ];

        $rows = array_map(function ($p) use ($now) {
            return array_merge($p, [
                'ulid'       => (string) Str::ulid(),
                'brand_name' => null,
                'is_active'  => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $products);

        DB::table('products')->upsert(
            $rows,
            ['sku_code'],
            ['generic_name', 'therapeutic_category', 'unit_size', 'description', 'updated_at']
        );

        $this->command->info('Products seeded: ' . count($rows) . ' SKUs');
    }
}
