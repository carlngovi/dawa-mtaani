<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);   // 1 — roles first, always
        $this->call(SystemSettingsSeeder::class);        // 2 — platform config
        $this->call(SuperAdminSeeder::class);            // 3 — first user (needs roles)
        $this->call(WhatsAppTemplateSeeder::class);      // 4 — notification templates
        $this->call(DataRetentionPolicySeeder::class);   // 5 — DPA retention defaults
        $this->call(KenyaGeographySeeder::class);        // 6 — 47 counties, 290 constituencies, 1,450 wards
        $this->call(ProductsSeeder::class);              // 7 — 49-SKU pilot catalogue
        $this->call(PpbRegistryCacheSeeder::class);      // 8 — 7,382 PPB registry records (needs CSV files)
        $this->call(TestUsersSeeder::class);             // 9 — 14 test accounts (dev/QA only, skips in production)
        $this->call(ProductAndPriceSeeder::class);       // 10 — products from CSV + wholesale price lists
        $this->call(ComprehensiveDemoSeeder::class);     // 11 — PPB facilities, users, price lists from CSVs
    }
}