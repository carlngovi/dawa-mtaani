<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevDataSeeder extends Seeder
{
    private Carbon $now;
    private string $password;

    public function run(): void
    {
        $this->now = Carbon::now('UTC');
        $this->password = Hash::make('ChangeMe2024!');

        $this->seedSystemSettings();
        $this->seedFacilities();
        $this->seedPharmacyGroups();
        $this->seedUsers();
        $this->seedProducts();
        $this->seedWholesalePriceLists();
        $this->seedFacilityStockStatus();
        $this->seedOrders();
        $this->seedDeliveryConfirmations();
        $this->seedSavedCarts();
        $this->seedFavouriteProducts();
        $this->seedPpbRegistryCache();
        $this->seedRecruiterData();
        $this->seedQualityFlags();
        $this->seedFacilityFlags();
        $this->seedWhatsAppSessions();
        $this->seedPricingAgreements();
        $this->seedNetworkDailySummaries();
    }

    // -------------------------------------------------------
    // SYSTEM SETTINGS
    // -------------------------------------------------------
    private function seedSystemSettings(): void
    {
        try {
            $settings = [
                'confirmation_clock_hours' => '72',
                'dispute_sla_hours' => '48',
                'ppb_registry_stale_days' => '7',
                'ppb_verification_mode' => 'FILE',
                'mfa_credit_draw_threshold_kes' => '50000',
                'ppb_csv_column_map' => '{"licence_number":0,"facility_name":1,"ppb_type":2,"licence_status":3,"registered_address":4,"licence_expiry_date":5}',
                'currency_iso_code' => 'KES',
                'currency_symbol' => 'KSh',
                'currency_decimal_places' => '2',
            ];

            foreach ($settings as $key => $value) {
                DB::table('system_settings')->upsert(
                    ['key' => $key, 'value' => $value, 'created_at' => $this->now, 'updated_at' => $this->now],
                    ['key'],
                    ['value', 'updated_at']
                );
            }

            $this->command->info('✓ System settings verified/upserted');
        } catch (\Throwable $e) {
            $this->command->error('✗ System settings: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // FACILITIES
    // -------------------------------------------------------
    private function seedFacilities(): void
    {
        try {
            $wholesalers = [
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Nairobi Pharma Wholesalers Ltd',
                    'owner_name' => 'James Kariuki', 'ppb_licence_number' => 'PPB-WHL-001',
                    'ppb_facility_type' => 'WHOLESALE', 'phone' => '+254720100001',
                    'county' => 'Nairobi', 'sub_county' => 'Westlands', 'ward' => 'Parklands',
                    'physical_address' => 'Enterprise Road, Industrial Area, Nairobi',
                    'latitude' => -1.3044, 'longitude' => 36.8372,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Mombasa Medical Supplies Ltd',
                    'owner_name' => 'Fatima Hassan', 'ppb_licence_number' => 'PPB-WHL-002',
                    'ppb_facility_type' => 'WHOLESALE', 'phone' => '+254720100002',
                    'county' => 'Mombasa', 'sub_county' => 'Mvita', 'ward' => 'Ganjoni',
                    'physical_address' => 'Moi Avenue, Mombasa CBD',
                    'latitude' => -4.0435, 'longitude' => 39.6682,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Rift Valley Pharma Distributors',
                    'owner_name' => 'Daniel Korir', 'ppb_licence_number' => 'PPB-MFG-001',
                    'ppb_facility_type' => 'MANUFACTURER', 'phone' => '+254720100003',
                    'county' => 'Nakuru', 'sub_county' => 'Nakuru East', 'ward' => 'Biashara',
                    'physical_address' => 'Kenyatta Avenue, Nakuru Town',
                    'latitude' => -0.2830, 'longitude' => 36.0800,
                    'network_membership' => 'NETWORK',
                ],
            ];

            $retailers = [
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Grace Pharmacy',
                    'owner_name' => 'Grace Wanjiku', 'ppb_licence_number' => 'PPB-RET-001',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200001',
                    'county' => 'Nairobi', 'sub_county' => 'Kasarani', 'ward' => 'Kasarani',
                    'physical_address' => 'Thika Road Mall, Kasarani',
                    'latitude' => -1.2220, 'longitude' => 36.8873,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Westside Chemist',
                    'owner_name' => 'Peter Mwangi', 'ppb_licence_number' => 'PPB-RET-002',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200002',
                    'county' => 'Nairobi', 'sub_county' => 'Westlands', 'ward' => 'Kangemi',
                    'physical_address' => 'Westlands Shopping Centre',
                    'latitude' => -1.2640, 'longitude' => 36.8040,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Diani Beach Pharmacy',
                    'owner_name' => 'Ali Omar', 'ppb_licence_number' => 'PPB-RET-003',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200003',
                    'county' => 'Kwale', 'sub_county' => 'Msambweni', 'ward' => 'Diani',
                    'physical_address' => 'Diani Beach Road, Ukunda',
                    'latitude' => -4.3177, 'longitude' => 39.5730,
                    'network_membership' => 'OFF_NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Kisumu Central Pharmacy',
                    'owner_name' => 'Atieno Odhiambo', 'ppb_licence_number' => 'PPB-RTL-004',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200004',
                    'county' => 'Kisumu', 'sub_county' => 'Kisumu Central', 'ward' => 'Kondele',
                    'physical_address' => 'Oginga Odinga Street, Kisumu',
                    'latitude' => -0.0917, 'longitude' => 34.7680,
                    'network_membership' => 'OFF_NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Eldoret Mediplus',
                    'owner_name' => 'Sharon Cherono', 'ppb_licence_number' => 'PPB-RET-005',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200005',
                    'county' => 'Uasin Gishu', 'sub_county' => 'Kapseret', 'ward' => 'Langas',
                    'physical_address' => 'Uganda Road, Eldoret CBD',
                    'latitude' => 0.5143, 'longitude' => 35.2698,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Coast General Dispensary',
                    'owner_name' => 'Dr. Rashid Ahmed', 'ppb_licence_number' => 'PPB-HOS-001',
                    'ppb_facility_type' => 'HOSPITAL', 'phone' => '+254720200006',
                    'county' => 'Mombasa', 'sub_county' => 'Changamwe', 'ward' => 'Port Reitz',
                    'physical_address' => 'Changamwe Road, Mombasa',
                    'latitude' => -4.0278, 'longitude' => 39.6243,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Thika Road Medicare',
                    'owner_name' => 'John Kamau', 'ppb_licence_number' => 'PPB-RET-006',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200007',
                    'county' => 'Kiambu', 'sub_county' => 'Thika', 'ward' => 'Kamenu',
                    'physical_address' => 'Kenyatta Highway, Thika Town',
                    'latitude' => -1.0396, 'longitude' => 37.0900,
                    'network_membership' => 'NETWORK',
                ],
                [
                    'ulid' => (string) Str::ulid(), 'facility_name' => 'Nakuru Wellness Pharmacy',
                    'owner_name' => 'Mary Wambui', 'ppb_licence_number' => 'PPB-RET-007',
                    'ppb_facility_type' => 'RETAIL', 'phone' => '+254720200008',
                    'county' => 'Nakuru', 'sub_county' => 'Nakuru East', 'ward' => 'Menengai',
                    'physical_address' => 'Nakuru CBD, Kenyatta Avenue',
                    'latitude' => -0.2827, 'longitude' => 36.0715,
                    'network_membership' => 'NETWORK',
                ],
            ];

            foreach (array_merge($wholesalers, $retailers) as $f) {
                if (DB::table('facilities')->where('ppb_licence_number', $f['ppb_licence_number'])->exists()) {
                    continue;
                }
                DB::table('facilities')->insert(array_merge($f, [
                    'ppb_licence_status' => 'VALID',
                    'ppb_verified_at' => $this->now,
                    'onboarding_status' => 'ACTIVE',
                    'facility_status' => 'ACTIVE',
                    'activated_at' => $this->now->copy()->subDays(rand(30, 90)),
                    'gps_accuracy_meters' => 10,
                    'gps_captured_at' => $this->now,
                    'gps_captured_by' => 1,
                    'gps_capture_method' => 'MANUAL_ENTRY',
                    'is_anonymised' => false,
                    'created_by' => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]));
            }

            $this->command->info('✓ Facilities seeded (3 wholesale + 8 retail)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Facilities: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // PHARMACY GROUPS
    // -------------------------------------------------------
    private function seedPharmacyGroups(): void
    {
        try {
            if (DB::table('pharmacy_groups')->where('group_name', 'Nairobi Retail Consortium')->exists()) {
                $this->command->info('✓ Pharmacy groups already exist');
                return;
            }

            $groupId = DB::table('pharmacy_groups')->insertGetId([
                'ulid' => (string) Str::ulid(),
                'group_name' => 'Nairobi Retail Consortium',
                'group_owner_name' => 'Peter Mwangi',
                'group_owner_phone' => '+254720200002',
                'group_owner_email' => 'peter@nairobiconsortium.co.ke',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            $memberLicences = ['PPB-RET-001', 'PPB-RET-002', 'PPB-RET-006'];
            foreach ($memberLicences as $licence) {
                $facilityId = DB::table('facilities')->where('ppb_licence_number', $licence)->value('id');
                if ($facilityId) {
                    DB::table('pharmacy_group_members')->insert([
                        'group_id' => $groupId, 'facility_id' => $facilityId,
                        'added_by' => 1, 'added_at' => $this->now,
                        'created_at' => $this->now, 'updated_at' => $this->now,
                    ]);
                }
            }

            $this->command->info('✓ Pharmacy group seeded (Nairobi Retail Consortium, 3 members)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Pharmacy groups: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // USERS
    // -------------------------------------------------------
    private function seedUsers(): void
    {
        try {
            // Link existing users
            $wholesaleFacilityId = DB::table('facilities')->where('ppb_licence_number', 'PPB-WHL-001')->value('id');
            $retailFacilityId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-001')->value('id');

            if ($wholesaleFacilityId) {
                DB::table('users')->where('email', 'wholesale@dawamtaani.local')
                    ->update(['facility_id' => $wholesaleFacilityId]);
            }
            if ($retailFacilityId) {
                DB::table('users')->where('email', 'retail@dawamtaani.local')
                    ->update(['facility_id' => $retailFacilityId]);
            }

            $newUsers = [
                [
                    'name' => 'Wholesale Mombasa User', 'email' => 'wholesale2@dawamtaani.local',
                    'phone' => '+254720300001', 'role' => 'wholesale_facility',
                    'facility_licence' => 'PPB-WHL-002',
                ],
                [
                    'name' => 'Retail Westside User', 'email' => 'retail2@dawamtaani.local',
                    'phone' => '+254720300002', 'role' => 'retail_facility',
                    'facility_licence' => 'PPB-RET-002',
                ],
                [
                    'name' => 'Retail Eldoret User', 'email' => 'retail3@dawamtaani.local',
                    'phone' => '+254720300003', 'role' => 'retail_facility',
                    'facility_licence' => 'PPB-RET-005',
                ],
                [
                    'name' => 'Field Agent Mombasa', 'email' => 'fieldagent2@dawamtaani.local',
                    'phone' => '+254720300004', 'role' => 'network_field_agent',
                    'facility_licence' => null,
                ],
            ];

            foreach ($newUsers as $u) {
                if (DB::table('users')->where('email', $u['email'])->exists()) continue;

                $facilityId = $u['facility_licence']
                    ? DB::table('facilities')->where('ppb_licence_number', $u['facility_licence'])->value('id')
                    : null;

                $userId = DB::table('users')->insertGetId([
                    'name' => $u['name'], 'email' => $u['email'],
                    'password' => $this->password, 'phone' => $u['phone'],
                    'facility_id' => $facilityId,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);

                DB::table('model_has_roles')->insert([
                    'role_id' => DB::table('roles')->where('name', $u['role'])->value('id'),
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                ]);
            }

            // Create authorised placers for retail facilities
            foreach (['PPB-RET-001', 'PPB-RET-002', 'PPB-RET-005'] as $licence) {
                $fId = DB::table('facilities')->where('ppb_licence_number', $licence)->value('id');
                $uId = DB::table('users')->where('facility_id', $fId)->value('id');
                if ($fId && $uId && !DB::table('facility_authorised_placers')->where('facility_id', $fId)->where('user_id', $uId)->exists()) {
                    DB::table('facility_authorised_placers')->insert([
                        'facility_id' => $fId, 'user_id' => $uId,
                        'added_by' => 1, 'added_at' => $this->now,
                        'is_active' => true, 'created_at' => $this->now, 'updated_at' => $this->now,
                    ]);
                }
            }

            $this->command->info('✓ Users seeded and linked to facilities');
        } catch (\Throwable $e) {
            $this->command->error('✗ Users: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // PRODUCTS (25 SKUs)
    // -------------------------------------------------------
    private function seedProducts(): void
    {
        try {
            $products = [
                ['sku' => 'AMX500', 'generic' => 'Amoxicillin 500mg', 'brand' => 'Amoxil', 'cat' => 'Antibiotics', 'unit' => '20 Capsules'],
                ['sku' => 'AZI500', 'generic' => 'Azithromycin 500mg', 'brand' => 'Zithromax', 'cat' => 'Antibiotics', 'unit' => '3 Tablets'],
                ['sku' => 'CIP500', 'generic' => 'Ciprofloxacin 500mg', 'brand' => 'Cipro', 'cat' => 'Antibiotics', 'unit' => '10 Tablets'],
                ['sku' => 'MTZ400', 'generic' => 'Metronidazole 400mg', 'brand' => 'Flagyl', 'cat' => 'Antibiotics', 'unit' => '21 Tablets'],
                ['sku' => 'DOX100', 'generic' => 'Doxycycline 100mg', 'brand' => 'Vibramycin', 'cat' => 'Antibiotics', 'unit' => '10 Capsules'],
                ['sku' => 'ACT20', 'generic' => 'Artemether/Lumefantrine 20/120mg', 'brand' => 'Coartem', 'cat' => 'Antimalarials', 'unit' => '24 Tablets'],
                ['sku' => 'QUI300', 'generic' => 'Quinine 300mg', 'brand' => null, 'cat' => 'Antimalarials', 'unit' => '30 Tablets'],
                ['sku' => 'PRI15', 'generic' => 'Primaquine 15mg', 'brand' => null, 'cat' => 'Antimalarials', 'unit' => '14 Tablets'],
                ['sku' => 'PCM500', 'generic' => 'Paracetamol 500mg', 'brand' => 'Panadol', 'cat' => 'Analgesics', 'unit' => '100 Tablets'],
                ['sku' => 'IBU400', 'generic' => 'Ibuprofen 400mg', 'brand' => 'Brufen', 'cat' => 'Analgesics', 'unit' => '30 Tablets'],
                ['sku' => 'DCL50', 'generic' => 'Diclofenac 50mg', 'brand' => 'Voltaren', 'cat' => 'Analgesics', 'unit' => '30 Tablets'],
                ['sku' => 'MET500', 'generic' => 'Metformin 500mg', 'brand' => 'Glucophage', 'cat' => 'Diabetes', 'unit' => '30 Tablets'],
                ['sku' => 'GLB5', 'generic' => 'Glibenclamide 5mg', 'brand' => 'Daonil', 'cat' => 'Diabetes', 'unit' => '30 Tablets'],
                ['sku' => 'INS100', 'generic' => 'Insulin Regular 100IU/ml', 'brand' => 'Humulin R', 'cat' => 'Diabetes', 'unit' => '10ml Vial'],
                ['sku' => 'AML5', 'generic' => 'Amlodipine 5mg', 'brand' => 'Norvasc', 'cat' => 'Cardiovascular', 'unit' => '30 Tablets'],
                ['sku' => 'ATR10', 'generic' => 'Atorvastatin 10mg', 'brand' => 'Lipitor', 'cat' => 'Cardiovascular', 'unit' => '30 Tablets'],
                ['sku' => 'ENA5', 'generic' => 'Enalapril 5mg', 'brand' => 'Renitec', 'cat' => 'Cardiovascular', 'unit' => '28 Tablets'],
                ['sku' => 'OMP20', 'generic' => 'Omeprazole 20mg', 'brand' => 'Losec', 'cat' => 'Gastrointestinal', 'unit' => '14 Capsules'],
                ['sku' => 'ORS1L', 'generic' => 'ORS 1L Sachets', 'brand' => null, 'cat' => 'Gastrointestinal', 'unit' => '10 Sachets'],
                ['sku' => 'LOP2', 'generic' => 'Loperamide 2mg', 'brand' => 'Imodium', 'cat' => 'Gastrointestinal', 'unit' => '8 Capsules'],
                ['sku' => 'VTC1', 'generic' => 'Vitamin C 1000mg', 'brand' => null, 'cat' => 'Vitamins & Supplements', 'unit' => '30 Tablets'],
                ['sku' => 'ZNC20', 'generic' => 'Zinc Sulphate 20mg', 'brand' => null, 'cat' => 'Vitamins & Supplements', 'unit' => '10 Tablets'],
                ['sku' => 'FOL5', 'generic' => 'Folic Acid 5mg', 'brand' => null, 'cat' => 'Vitamins & Supplements', 'unit' => '30 Tablets'],
                ['sku' => 'CTZ10', 'generic' => 'Cetirizine 10mg', 'brand' => 'Zyrtec', 'cat' => 'Antihistamines', 'unit' => '30 Tablets'],
                ['sku' => 'LOR10', 'generic' => 'Loratadine 10mg', 'brand' => 'Claritin', 'cat' => 'Antihistamines', 'unit' => '30 Tablets'],
            ];

            foreach ($products as $p) {
                if (DB::table('products')->where('sku_code', $p['sku'])->exists()) continue;
                DB::table('products')->insert([
                    'ulid' => (string) Str::ulid(), 'sku_code' => $p['sku'],
                    'generic_name' => $p['generic'], 'brand_name' => $p['brand'],
                    'therapeutic_category' => $p['cat'], 'unit_size' => $p['unit'],
                    'is_active' => true, 'created_by' => 1,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);
            }

            $this->command->info('✓ Products seeded (25 SKUs across 8 categories)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Products: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // WHOLESALE PRICE LISTS
    // -------------------------------------------------------
    private function seedWholesalePriceLists(): void
    {
        try {
            $wholesaleIds = DB::table('facilities')
                ->whereIn('ppb_facility_type', ['WHOLESALE', 'MANUFACTURER'])
                ->pluck('id');

            $productIds = DB::table('products')->pluck('id');
            $today = $this->now->toDateString();

            $basePrices = [
                'AMX500' => 350, 'AZI500' => 850, 'CIP500' => 450, 'MTZ400' => 280, 'DOX100' => 320,
                'ACT20' => 650, 'QUI300' => 520, 'PRI15' => 380, 'PCM500' => 180, 'IBU400' => 250,
                'DCL50' => 300, 'MET500' => 420, 'GLB5' => 280, 'INS100' => 3200, 'AML5' => 380,
                'ATR10' => 550, 'ENA5' => 350, 'OMP20' => 480, 'ORS1L' => 150, 'LOP2' => 220,
                'VTC1' => 350, 'ZNC20' => 180, 'FOL5' => 160, 'CTZ10' => 280, 'LOR10' => 320,
            ];

            $products = DB::table('products')->get();
            $inserted = 0;

            foreach ($wholesaleIds as $wId) {
                foreach ($products as $product) {
                    if (DB::table('wholesale_price_lists')->where('wholesale_facility_id', $wId)->where('product_id', $product->id)->exists()) continue;

                    $base = $basePrices[$product->sku_code] ?? 300;
                    $price = $base * (1 + (rand(-10, 15) / 100)); // ±10-15% variation per wholesaler

                    $rand = rand(1, 100);
                    $stockStatus = $rand <= 70 ? 'IN_STOCK' : ($rand <= 90 ? 'LOW_STOCK' : 'OUT_OF_STOCK');

                    DB::table('wholesale_price_lists')->insert([
                        'wholesale_facility_id' => $wId, 'product_id' => $product->id,
                        'unit_price' => round($price, 2), 'effective_from' => $today,
                        'expires_at' => null, 'stock_status' => $stockStatus,
                        'stock_quantity' => $stockStatus === 'OUT_OF_STOCK' ? 0 : rand(10, 500),
                        'is_active' => true, 'created_at' => $this->now, 'updated_at' => $this->now,
                    ]);
                    $inserted++;
                }
            }

            $this->command->info("✓ Wholesale price lists seeded ({$inserted} entries across 3 wholesalers)");
        } catch (\Throwable $e) {
            $this->command->error('✗ Wholesale price lists: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // FACILITY STOCK STATUS
    // -------------------------------------------------------
    private function seedFacilityStockStatus(): void
    {
        try {
            $priceLists = DB::table('wholesale_price_lists')->get();
            $inserted = 0;

            foreach ($priceLists as $pl) {
                if (DB::table('facility_stock_status')->where('wholesale_facility_id', $pl->wholesale_facility_id)->where('product_id', $pl->product_id)->exists()) continue;
                DB::table('facility_stock_status')->insert([
                    'wholesale_facility_id' => $pl->wholesale_facility_id,
                    'product_id' => $pl->product_id,
                    'stock_status' => $pl->stock_status,
                    'stock_quantity' => $pl->stock_quantity,
                    'updated_by' => 1,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);
                $inserted++;
            }

            $this->command->info("✓ Facility stock status seeded ({$inserted} records)");
        } catch (\Throwable $e) {
            $this->command->error('✗ Facility stock status: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // ORDERS (15 sample)
    // -------------------------------------------------------
    private function seedOrders(): void
    {
        try {
            if (DB::table('orders')->count() >= 15) {
                $this->command->info('✓ Orders already seeded');
                return;
            }

            $retailFacilities = DB::table('facilities')
                ->whereIn('ppb_facility_type', ['RETAIL', 'HOSPITAL'])
                ->get();

            $wholesaleIds = DB::table('facilities')
                ->whereIn('ppb_facility_type', ['WHOLESALE', 'MANUFACTURER'])
                ->pluck('id')->toArray();

            $statuses = ['PENDING', 'CONFIRMED', 'DISPATCHED', 'DELIVERED', 'DELIVERED', 'DELIVERED'];
            $channels = ['WEB', 'WEB', 'WEB', 'WEB', 'WHATSAPP', 'WHATSAPP', 'OFFLINE_QR'];

            $products = DB::table('products')->get();

            for ($i = 0; $i < 15; $i++) {
                $facility = $retailFacilities->random();
                $isNetwork = $facility->network_membership === 'NETWORK';
                $status = $statuses[array_rand($statuses)];
                $channel = $channels[array_rand($channels)];
                $orderType = $isNetwork ? (rand(0, 1) ? 'CREDIT' : 'CASH') : 'OFF_NETWORK_CASH';
                $createdAt = $this->now->copy()->subDays(rand(1, 30))->subHours(rand(0, 12));

                $userId = DB::table('users')->where('facility_id', $facility->id)->value('id')
                    ?? DB::table('users')->where('email', 'retail@dawamtaani.local')->value('id');

                $lineCount = rand(2, 4);
                $totalAmount = 0;
                $lines = [];

                for ($j = 0; $j < $lineCount; $j++) {
                    $product = $products->random();
                    $wId = $wholesaleIds[array_rand($wholesaleIds)];
                    $priceList = DB::table('wholesale_price_lists')
                        ->where('wholesale_facility_id', $wId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (!$priceList) continue;

                    $qty = rand(1, 20);
                    $lineTotal = round($priceList->unit_price * $qty, 2);
                    $totalAmount += $lineTotal;

                    $lines[] = [
                        'wholesale_facility_id' => $wId,
                        'product_id' => $product->id,
                        'price_list_id' => $priceList->id,
                        'quantity' => $qty,
                        'unit_price' => $priceList->unit_price,
                        'premium_applied' => false,
                        'premium_amount' => 0,
                        'line_total' => $lineTotal,
                        'payment_type' => $isNetwork ? ($orderType === 'CREDIT' ? 'CREDIT' : 'CASH') : 'OFF_NETWORK_CASH',
                        'placer_user_id' => $userId,
                    ];
                }

                if (empty($lines)) continue;

                $creditAmount = $orderType === 'CREDIT' ? $totalAmount : 0;
                $cashAmount = $orderType !== 'CREDIT' ? $totalAmount : 0;

                $orderId = DB::table('orders')->insertGetId([
                    'ulid' => (string) Str::ulid(),
                    'retail_facility_id' => $facility->id,
                    'placed_by_user_id' => $userId,
                    'is_group_order' => false,
                    'is_network_member' => $isNetwork,
                    'order_type' => $orderType,
                    'source_channel' => $channel,
                    'status' => $status,
                    'total_amount' => round($totalAmount, 2),
                    'credit_amount' => round($creditAmount, 2),
                    'cash_amount' => round($cashAmount, 2),
                    'copay_status' => 'NOT_REQUIRED',
                    'submitted_at' => $createdAt,
                    'confirmed_at' => in_array($status, ['CONFIRMED', 'DISPATCHED', 'DELIVERED']) ? $createdAt->copy()->addHours(2) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($lines as $line) {
                    DB::table('order_lines')->insert(array_merge($line, [
                        'order_id' => $orderId,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]));
                }
            }

            $this->command->info('✓ Orders seeded (15 orders with 2-4 lines each)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Orders: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // DELIVERY CONFIRMATIONS
    // -------------------------------------------------------
    private function seedDeliveryConfirmations(): void
    {
        try {
            $delivered = DB::table('orders')->where('status', 'DELIVERED')->get();
            $logisticsId = DB::table('facilities')->where('ppb_facility_type', 'WHOLESALE')->value('id');
            $inserted = 0;

            foreach ($delivered as $order) {
                if (DB::table('delivery_confirmations')->where('order_id', $order->id)->exists()) continue;

                $deliveredAt = Carbon::parse($order->created_at)->addDays(1);
                $confirmedAt = $deliveredAt->copy()->addDays(1);

                DB::table('delivery_confirmations')->insert([
                    'order_id' => $order->id,
                    'logistics_facility_id' => $logisticsId,
                    'delivered_at' => $deliveredAt,
                    'pod_photo_path' => 'dev/pod_placeholder.jpg',
                    'confirmation_clock_started_at' => $deliveredAt,
                    'confirmation_deadline_at' => $deliveredAt->copy()->addHours(72),
                    'confirmed_at' => $confirmedAt,
                    'confirmed_by' => 1,
                    'confirmation_type' => 'RETAIL_CONFIRMED',
                    'created_at' => $deliveredAt,
                    'updated_at' => $confirmedAt,
                ]);
                $inserted++;
            }

            $this->command->info("✓ Delivery confirmations seeded ({$inserted} records)");
        } catch (\Throwable $e) {
            $this->command->error('✗ Delivery confirmations: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // SAVED CARTS
    // -------------------------------------------------------
    private function seedSavedCarts(): void
    {
        try {
            $graceId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-001')->value('id');
            if (!$graceId || DB::table('saved_carts')->where('owner_facility_id', $graceId)->exists()) {
                $this->command->info('✓ Saved carts already exist');
                return;
            }

            $products = DB::table('products')->get()->keyBy('sku_code');
            $wId = DB::table('facilities')->where('ppb_licence_number', 'PPB-WHL-001')->value('id');

            // Cart 1: Weekly Antibiotics
            $cart1Id = DB::table('saved_carts')->insertGetId([
                'ulid' => (string) Str::ulid(), 'name' => 'Weekly Antibiotics Order',
                'owner_facility_id' => $graceId, 'is_group_cart' => false,
                'created_by' => 1, 'created_at' => $this->now, 'updated_at' => $this->now,
            ]);
            foreach (['AMX500' => 20, 'AZI500' => 10, 'CIP500' => 15] as $sku => $qty) {
                DB::table('saved_cart_lines')->insert([
                    'saved_cart_id' => $cart1Id, 'product_id' => $products[$sku]->id,
                    'wholesale_facility_id' => $wId, 'quantity' => $qty,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);
            }

            // Cart 2: Monthly Diabetes Stock
            $cart2Id = DB::table('saved_carts')->insertGetId([
                'ulid' => (string) Str::ulid(), 'name' => 'Monthly Diabetes Stock',
                'owner_facility_id' => $graceId, 'is_group_cart' => false,
                'created_by' => 1, 'created_at' => $this->now, 'updated_at' => $this->now,
            ]);
            foreach (['MET500' => 30, 'GLB5' => 15, 'INS100' => 5, 'AML5' => 20] as $sku => $qty) {
                DB::table('saved_cart_lines')->insert([
                    'saved_cart_id' => $cart2Id, 'product_id' => $products[$sku]->id,
                    'wholesale_facility_id' => $wId, 'quantity' => $qty,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);
            }

            $this->command->info('✓ Saved carts seeded (2 carts for Grace Pharmacy)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Saved carts: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // FAVOURITE PRODUCTS
    // -------------------------------------------------------
    private function seedFavouriteProducts(): void
    {
        try {
            $graceId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-001')->value('id');
            $westsideId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-002')->value('id');
            $products = DB::table('products')->get()->keyBy('sku_code');

            $favourites = [
                $graceId => ['AMX500', 'PCM500', 'OMP20', 'MET500', 'ACT20'],
                $westsideId => ['PCM500', 'IBU400', 'CTZ10', 'VTC1'],
            ];

            $inserted = 0;
            foreach ($favourites as $fId => $skus) {
                if (!$fId) continue;
                foreach ($skus as $sku) {
                    $pId = $products[$sku]->id ?? null;
                    if (!$pId || DB::table('facility_favourite_products')->where('facility_id', $fId)->where('product_id', $pId)->exists()) continue;
                    DB::table('facility_favourite_products')->insert([
                        'facility_id' => $fId, 'product_id' => $pId, 'added_by' => 1,
                        'created_at' => $this->now, 'updated_at' => $this->now,
                    ]);
                    $inserted++;
                }
            }

            $this->command->info("✓ Favourite products seeded ({$inserted} records)");
        } catch (\Throwable $e) {
            $this->command->error('✗ Favourite products: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // PPB REGISTRY CACHE
    // -------------------------------------------------------
    private function seedPpbRegistryCache(): void
    {
        try {
            $facilities = DB::table('facilities')->get();
            $inserted = 0;

            // Create upload record
            $uploadId = null;
            if (!DB::table('ppb_registry_uploads')->where('file_name', 'dev_seed_registry.csv')->exists()) {
                $uploadId = DB::table('ppb_registry_uploads')->insertGetId([
                    'uploaded_by' => 1, 'file_name' => 'dev_seed_registry.csv',
                    'file_hash' => hash('sha256', 'dev_seed_' . time()),
                    'row_count' => $facilities->count(), 'rows_inserted' => $facilities->count(),
                    'rows_updated' => 0, 'rows_rejected' => 0,
                    'status' => 'COMPLETED', 'uploaded_at' => $this->now,
                ]);
            } else {
                $uploadId = DB::table('ppb_registry_uploads')->where('file_name', 'dev_seed_registry.csv')->value('id');
            }

            foreach ($facilities as $f) {
                if (DB::table('ppb_registry_cache')->where('licence_number', $f->ppb_licence_number)->exists()) continue;
                DB::table('ppb_registry_cache')->insert([
                    'licence_number' => $f->ppb_licence_number,
                    'facility_name' => $f->facility_name,
                    'ppb_type' => $f->ppb_facility_type,
                    'licence_status' => 'VALID',
                    'registered_address' => $f->physical_address,
                    'licence_expiry_date' => $this->now->copy()->addYear()->toDateString(),
                    'last_uploaded_at' => $this->now,
                    'upload_batch_id' => $uploadId,
                    'created_at' => $this->now, 'updated_at' => $this->now,
                ]);
                $inserted++;
            }

            $this->command->info("✓ PPB registry cache seeded ({$inserted} records + upload record)");
        } catch (\Throwable $e) {
            $this->command->error('✗ PPB registry cache: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // RECRUITER DATA
    // -------------------------------------------------------
    private function seedRecruiterData(): void
    {
        try {
            if (DB::table('recruiter_firms')->where('firm_name', 'Afya Agents Kenya')->exists()) {
                $this->command->info('✓ Recruiter data already exists');
                return;
            }

            $firmId = DB::table('recruiter_firms')->insertGetId([
                'firm_name' => 'Afya Agents Kenya',
                'commission_rate_kes' => 0.00,
                'cascade_config' => json_encode(['type' => 'full_cascade', 'levels' => [['level' => 0, 'pct' => 60], ['level' => 1, 'pct' => 40]]]),
                'bank_account_details' => 'KCB 1234567890 — Afya Agents Kenya Ltd',
                'status' => 'ACTIVE',
                'created_at' => $this->now, 'updated_at' => $this->now,
            ]);

            $samuelId = DB::table('recruiter_agents')->insertGetId([
                'firm_id' => $firmId, 'parent_agent_id' => null,
                'agent_name' => 'Samuel Ochieng', 'agent_phone' => '+254711000001',
                'agent_role_label' => 'Regional Manager', 'status' => 'ACTIVE',
                'created_at' => $this->now,
            ]);

            $beatriceId = DB::table('recruiter_agents')->insertGetId([
                'firm_id' => $firmId, 'parent_agent_id' => $samuelId,
                'agent_name' => 'Beatrice Njeri', 'agent_phone' => '+254711000002',
                'agent_role_label' => 'Area Agent', 'status' => 'ACTIVE',
                'created_at' => $this->now,
            ]);

            DB::table('recruiter_agents')->insert([
                'firm_id' => $firmId, 'parent_agent_id' => $samuelId,
                'agent_name' => 'David Mutua', 'agent_phone' => '+254711000003',
                'agent_role_label' => 'Area Agent', 'status' => 'ACTIVE',
                'created_at' => $this->now,
            ]);

            // Commission triggers
            foreach ([
                ['trigger_event' => 'PHARMACY_REGISTRATION', 'threshold_value' => null],
                ['trigger_event' => 'FIRST_ORDER_PLACED', 'threshold_value' => null],
                ['trigger_event' => 'NTH_ORDER_PLACED', 'threshold_value' => 10],
            ] as $trigger) {
                DB::table('recruiter_commission_triggers')->insert(array_merge($trigger, [
                    'firm_id' => $firmId, 'is_active' => true,
                ]));
            }

            // Activation events
            $graceId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-001')->value('id');
            $westsideId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-002')->value('id');

            foreach ([
                ['agent_id' => $samuelId, 'facility_id' => $graceId],
                ['agent_id' => $beatriceId, 'facility_id' => $westsideId],
            ] as $activation) {
                if (!$activation['facility_id']) continue;
                DB::table('recruiter_activation_events')->insert([
                    'firm_id' => $firmId, 'agent_id' => $activation['agent_id'],
                    'facility_id' => $activation['facility_id'],
                    'trigger_event' => 'PHARMACY_REGISTRATION',
                    'gross_amount_kes' => 0.00,
                    'cascade_breakdown' => json_encode([['agent_id' => null, 'type' => 'firm', 'amount' => 0]]),
                    'reconciliation_status' => 'PENDING',
                    'created_at' => $this->now,
                ]);
            }

            $this->command->info('✓ Recruiter data seeded (1 firm, 3 agents, 3 triggers, 2 activations)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Recruiter data: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // QUALITY FLAGS
    // -------------------------------------------------------
    private function seedQualityFlags(): void
    {
        try {
            if (DB::table('quality_flags')->count() >= 3) {
                $this->command->info('✓ Quality flags already exist');
                return;
            }

            $products = DB::table('products')->get()->keyBy('sku_code');
            $flags = [
                ['licence' => 'PPB-RET-001', 'sku' => 'AMX500', 'type' => 'SUSPECTED_COUNTERFEIT', 'status' => 'OPEN', 'notes' => 'Unusual packaging colour on recent batch'],
                ['licence' => 'PPB-RET-002', 'sku' => 'ACT20', 'type' => 'PACKAGING_ANOMALY', 'status' => 'UNDER_REVIEW', 'notes' => 'Blister pack seal appears tampered'],
                ['licence' => 'PPB-RET-005', 'sku' => 'PCM500', 'type' => 'LABELLING_CONCERN', 'status' => 'DISMISSED', 'notes' => 'Expiry date printed in wrong format'],
            ];

            foreach ($flags as $f) {
                $facilityId = DB::table('facilities')->where('ppb_licence_number', $f['licence'])->value('id');
                $productId = $products[$f['sku']]->id ?? null;
                if (!$facilityId || !$productId) continue;

                DB::table('quality_flags')->insert([
                    'ulid' => (string) Str::ulid(), 'facility_id' => $facilityId,
                    'product_id' => $productId, 'flag_type' => $f['type'],
                    'status' => $f['status'], 'notes' => $f['notes'],
                    'reviewed_by' => $f['status'] !== 'OPEN' ? 1 : null,
                    'created_at' => $this->now->copy()->subDays(rand(1, 14)),
                    'updated_at' => $this->now,
                ]);
            }

            $this->command->info('✓ Quality flags seeded (3 flags)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Quality flags: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // FACILITY FLAGS
    // -------------------------------------------------------
    private function seedFacilityFlags(): void
    {
        try {
            if (DB::table('facility_flags')->count() >= 2) {
                $this->command->info('✓ Facility flags already exist');
                return;
            }

            $flags = [
                ['licence' => 'PPB-RTL-004', 'reason' => 'LATE_PAYMENT', 'notes' => 'Overdue by 45 days'],
                ['licence' => 'PPB-RET-003', 'reason' => 'LOW_ORDER_FREQUENCY', 'notes' => 'No orders in 60 days'],
            ];

            foreach ($flags as $f) {
                $facilityId = DB::table('facilities')->where('ppb_licence_number', $f['licence'])->value('id');
                if (!$facilityId) continue;

                DB::table('facility_flags')->insert([
                    'facility_id' => $facilityId, 'flagged_by' => 1,
                    'reason' => $f['reason'], 'notes' => $f['notes'],
                    'created_at' => $this->now->copy()->subDays(rand(5, 20)),
                    'updated_at' => $this->now,
                ]);
            }

            $this->command->info('✓ Facility flags seeded (2 flags)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Facility flags: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // WHATSAPP SESSIONS
    // -------------------------------------------------------
    private function seedWhatsAppSessions(): void
    {
        try {
            if (DB::table('whatsapp_sessions')->count() >= 2) {
                $this->command->info('✓ WhatsApp sessions already exist');
                return;
            }

            $graceId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-001')->value('id');
            $westsideId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RET-002')->value('id');

            DB::table('whatsapp_sessions')->insert([
                'facility_id' => $graceId, 'whatsapp_phone' => '+254700000099',
                'session_state' => 'IDLE', 'last_activity_at' => $this->now,
                'expires_at' => $this->now->copy()->addMinutes(30),
                'authenticated_at' => $this->now, 'authentication_method' => 'LINKED_PHONE',
                'created_at' => $this->now, 'updated_at' => $this->now,
            ]);

            $firstProductId = DB::table('products')->where('sku_code', 'AMX500')->value('id');
            DB::table('whatsapp_sessions')->insert([
                'facility_id' => $westsideId, 'whatsapp_phone' => '+254700000088',
                'session_state' => 'ORDER_BUILDING',
                'session_context' => json_encode(['lines' => [['product_id' => $firstProductId, 'sku_code' => 'AMX500', 'quantity' => 10]]]),
                'last_activity_at' => $this->now, 'expires_at' => $this->now->copy()->addMinutes(30),
                'authenticated_at' => $this->now, 'authentication_method' => 'LINKED_PHONE',
                'created_at' => $this->now, 'updated_at' => $this->now,
            ]);

            $this->command->info('✓ WhatsApp sessions seeded (2 sessions)');
        } catch (\Throwable $e) {
            $this->command->error('✗ WhatsApp sessions: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // PRICING AGREEMENTS
    // -------------------------------------------------------
    private function seedPricingAgreements(): void
    {
        try {
            $kisumuId = DB::table('facilities')->where('ppb_licence_number', 'PPB-RTL-004')->value('id');
            if (!$kisumuId || DB::table('facility_pricing_agreements')->where('facility_id', $kisumuId)->exists()) {
                $this->command->info('✓ Pricing agreements already exist');
                return;
            }

            DB::table('facility_pricing_agreements')->insert([
                'facility_id' => $kisumuId, 'premium_type' => 'PERCENTAGE',
                'premium_value' => 8.5000, 'effective_from' => $this->now->toDateString(),
                'expires_at' => null, 'agreed_by' => 1,
                'created_at' => $this->now, 'updated_at' => $this->now,
            ]);

            $this->command->info('✓ Pricing agreements seeded (1 off-network agreement)');
        } catch (\Throwable $e) {
            $this->command->error('✗ Pricing agreements: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // NETWORK DAILY SUMMARIES (7 days)
    // -------------------------------------------------------
    private function seedNetworkDailySummaries(): void
    {
        try {
            $inserted = 0;

            for ($d = 6; $d >= 0; $d--) {
                $date = $this->now->copy()->subDays($d)->toDateString();

                $segments = [
                    ['membership' => 'NETWORK', 'gmv_min' => 80000, 'gmv_max' => 200000, 'orders_min' => 15, 'orders_max' => 45],
                    ['membership' => 'OFF_NETWORK', 'gmv_min' => 15000, 'gmv_max' => 40000, 'orders_min' => 3, 'orders_max' => 12],
                ];

                foreach ($segments as $seg) {
                    $totalOrders = rand($seg['orders_min'], $seg['orders_max']);
                    $totalGmv = rand($seg['gmv_min'], $seg['gmv_max']);
                    $avgOrder = $totalOrders > 0 ? round($totalGmv / $totalOrders, 2) : 0;

                    $row = [
                        'summary_date' => $date,
                        'county' => null,
                        'facility_type' => null,
                        'network_membership' => $seg['membership'],
                        'total_orders' => $totalOrders,
                        'total_gmv' => $totalGmv,
                        'avg_order_value' => $avgOrder,
                        'active_facilities' => rand(3, 8),
                        'new_facilities' => rand(0, 2),
                        'credit_drawn' => round($totalGmv * 0.6, 2),
                        'credit_repaid' => round($totalGmv * 0.4, 2),
                        'overdue_count' => rand(0, 3),
                        'overdue_value' => rand(0, 15000),
                        'computed_at' => $this->now,
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ];

                    DB::table('network_daily_summaries')->upsert(
                        [$row],
                        ['summary_date', 'county', 'network_membership', 'facility_type'],
                        ['total_orders', 'total_gmv', 'avg_order_value', 'active_facilities', 'new_facilities', 'credit_drawn', 'credit_repaid', 'overdue_count', 'overdue_value', 'computed_at', 'updated_at']
                    );
                    $inserted++;
                }

                // ALL segment = sum of NETWORK + OFF_NETWORK
                $networkRow = DB::table('network_daily_summaries')
                    ->where('summary_date', $date)->where('network_membership', 'NETWORK')
                    ->whereNull('county')->whereNull('facility_type')->first();
                $offNetworkRow = DB::table('network_daily_summaries')
                    ->where('summary_date', $date)->where('network_membership', 'OFF_NETWORK')
                    ->whereNull('county')->whereNull('facility_type')->first();

                if ($networkRow && $offNetworkRow) {
                    $allOrders = $networkRow->total_orders + $offNetworkRow->total_orders;
                    $allGmv = $networkRow->total_gmv + $offNetworkRow->total_gmv;

                    DB::table('network_daily_summaries')->upsert([[
                        'summary_date' => $date, 'county' => null, 'facility_type' => null,
                        'network_membership' => 'ALL',
                        'total_orders' => $allOrders,
                        'total_gmv' => $allGmv,
                        'avg_order_value' => $allOrders > 0 ? round($allGmv / $allOrders, 2) : 0,
                        'active_facilities' => $networkRow->active_facilities + $offNetworkRow->active_facilities,
                        'new_facilities' => $networkRow->new_facilities + $offNetworkRow->new_facilities,
                        'credit_drawn' => $networkRow->credit_drawn,
                        'credit_repaid' => $networkRow->credit_repaid,
                        'overdue_count' => $networkRow->overdue_count,
                        'overdue_value' => $networkRow->overdue_value,
                        'computed_at' => $this->now, 'created_at' => $this->now, 'updated_at' => $this->now,
                    ]], ['summary_date', 'county', 'network_membership', 'facility_type'],
                    ['total_orders', 'total_gmv', 'avg_order_value', 'active_facilities', 'new_facilities', 'credit_drawn', 'credit_repaid', 'overdue_count', 'overdue_value', 'computed_at', 'updated_at']);
                    $inserted++;
                }
            }

            $this->command->info("✓ Network daily summaries seeded ({$inserted} records, 7 days)");
        } catch (\Throwable $e) {
            $this->command->error('✗ Network daily summaries: ' . $e->getMessage());
        }
    }
}
