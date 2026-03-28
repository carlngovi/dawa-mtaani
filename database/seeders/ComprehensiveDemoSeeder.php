<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * ComprehensiveDemoSeeder
 *
 * Seeds a fully-linked live-feeling demo environment from the five CSVs in
 * database/seeders/data/:
 *   - expanded_50_skus_seed.csv  -> products + wholesale prices
 *   - PBB_Manufacturer.csv      -> manufacturer facilities + users
 *   - PBB_Retail.csv            -> retail pharmacy facilities + users
 *   - PBB_Wholesale.csv         -> wholesale distributor facilities + users
 *   - PPB_Hospitals.csv         -> hospital facilities (read-only / reference)
 *
 * All test accounts use Password@123
 * Emails follow the pattern: role.slug@dawamtaani.test
 *
 * Run with:
 *   php artisan db:seed --class=ComprehensiveDemoSeeder
 *
 * Safe to run multiple times -- uses updateOrCreate throughout.
 */
class ComprehensiveDemoSeeder extends Seeder
{
    // --- Paths ---------------------------------------------------------------
    private string $dataDir;

    // --- Shared password -----------------------------------------------------
    private string $passwordHash;

    // --- Auto-increment phone suffix for facilities --------------------------
    private int $phoneSuffix = 100;

    // --- Counters for summary ------------------------------------------------
    private array $counts = [
        'manufacturers' => 0,
        'wholesalers'   => 0,
        'retailers'     => 0,
        'hospitals'     => 0,
        'products'      => 0,
        'price_lists'   => 0,
        'users'         => 0,
    ];

    // --- Column-name aliases (PPB exports vary between downloads) ------------
    private array $colAliases = [
        'licence'    => ['Registration No','licence_number','licence_no','licence no',
                         'registration_no','reg_no','permit_no','pbb_licence','ppb_number',
                         'Licence No','Registration Number','Permit No','License Number'],
        'name'       => ['Facility Name','facility_name','business_name','company_name',
                         'name','pharmacy_name','hospital_name','distributor_name',
                         'Business Name','Company Name','Name','Organisation Name'],
        'county'     => ['County','county','county_name','County Name'],
        'sub_county' => ['sub_county','subcounty','sub county','Sub County',
                         'Sub-County','SubCounty'],
        'address'    => ['Street','physical_address','address','location',
                         'Physical Address','Address','Location','Physical Location'],
        'owner'      => ['Ownership','owner_name','proprietor','contact_person','Owner',
                         'Proprietor','Owner Name','Contact Person'],
        'phone'      => ['phone','telephone','contact','mobile','Phone',
                         'Telephone','Contact','Mobile'],
        'email'      => ['email','email_address','Email','Email Address'],
        'lat'        => ['latitude','lat','gps_lat','Latitude','GPS_Lat'],
        'lng'        => ['longitude','lng','lon','gps_lng','Longitude','GPS_Lng'],
    ];

    // --- Kenyan counties for random GPS fallback -----------------------------
    private array $countyCentroids = [
        'Nairobi'    => [-1.2921,  36.8219],
        'Mombasa'    => [-4.0435,  39.6682],
        'Kisumu'     => [-0.0917,  34.7680],
        'Nakuru'     => [-0.3031,  36.0800],
        'Eldoret'    => [0.5143,   35.2698],
        'Thika'      => [-1.0332,  37.0693],
        'Nyeri'      => [-0.4167,  36.9500],
        'Meru'       => [0.0469,   37.6496],
        'Garissa'    => [-0.4532,  42.0167],
        'Kakamega'   => [0.2827,   34.7519],
        'Kisii'      => [-0.6817,  34.7667],
        'Embu'       => [-0.5300,  37.4500],
        'Machakos'   => [-1.5177,  37.2634],
        'Kitui'      => [-1.3667,  38.0167],
        'Bungoma'    => [0.5635,   34.5606],
    ];

    // --- Wholesale base prices per SKU (used when building price lists) ------
    private array $skuBasePrices = [
        'MAL-001' => 165, 'MAL-002' => 95,  'MAL-003' => 55,
        'MAL-004' => 120, 'MAL-005' => 280,
        'ANT-ACC-001' => 180, 'ANT-ACC-002' => 180, 'ANT-ACC-003' => 180,
        'ANT-ACC-004' => 180, 'ANT-ACC-005' => 180, 'ANT-ACC-006' => 180,
        'ANT-ACC-007' => 180,
        'ANT-WCH-001' => 320, 'ANT-WCH-002' => 320,
        'REP-001' => 95,  'REP-002' => 220, 'REP-003' => 210,
        'REP-004' => 380, 'REP-005' => 85,
        'BAR-001' => 45,
        'CHR-001' => 85,  'CHR-002' => 95,  'CHR-003' => 120,
        'CHR-004' => 145, 'CHR-005' => 110, 'CHR-006' => 65,
        'PED-001' => 35,  'PED-002' => 45,  'PED-003' => 75,
        'VIT-001' => 95,  'VIT-002' => 65,  'VIT-003' => 120,
        'PAI-001' => 55,  'PAI-002' => 85,  'PAI-003' => 35, 'PAI-004' => 75,
        'GAS-001' => 145, 'GAS-002' => 85,  'GAS-003' => 35,
        'DEW-001' => 25,
        'HIV-001' => 180, 'HIV-002' => 95,
        'RES-001' => 420, 'RES-002' => 55,
        'FUN-001' => 195, 'FUN-002' => 85,
        'TOP-001' => 95,
        'DIA-001' => 850, 'DIA-002' => 1200,
    ];

    // -------------------------------------------------------------------------
    public function run(): void
    {
        $this->dataDir      = database_path('seeders/data');
        $this->passwordHash = Hash::make('Password@123');

        $this->command?->info('');
        $this->command?->info('========================================');
        $this->command?->info('  Dawa Mtaani - Comprehensive Demo Seeder');
        $this->command?->info('========================================');
        $this->command?->info('');

        DB::transaction(function () {
            $this->seedManufacturers();
            $this->seedWholesalers();
            $this->seedRetailers();
            $this->seedHospitals();
            $this->seedProducts();
            $this->seedWholesalePriceLists();
            $this->linkRetailersToWholesalers();
        });

        $this->printSummary();
    }

    // =========================================================================
    // MANUFACTURERS
    // =========================================================================
    private function seedManufacturers(): void
    {
        $this->command?->info('>> Seeding manufacturers...');
        $rows = $this->readCsv('PBB_Manufacturer.csv');
        $rows = array_slice($rows, 0, 8);

        foreach ($rows as $i => $row) {
            $name    = $this->col($row, 'name')    ?? "Manufacturer " . ($i + 1);
            $licence = $this->col($row, 'licence') ?? 'MFR-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $county  = $this->col($row, 'county')  ?? 'Nairobi';
            $address = $this->col($row, 'address') ?? $county . ', Kenya';
            [$lat, $lng] = $this->resolveGps($row, $county);

            $slug  = Str::slug($name);
            $email = substr("manufacturer.{$slug}@dawamtaani.test", 0, 80);

            $facilityId = $this->upsertFacility([
                'facility_name'      => $name,
                'ppb_licence_number' => $licence,
                'ppb_facility_type'  => 'MANUFACTURER',
                'facility_status'    => 'ACTIVE',
                'county'             => $county,
                'physical_address'   => $address,
                'latitude'           => $lat,
                'longitude'          => $lng,
                'network_membership' => 'NETWORK',
                'email'              => $email,
                'owner_name'         => $this->col($row, 'owner') ?? $name,
            ]);

            $this->upsertUser([
                'name'        => $this->col($row, 'owner') ?? "Manager - {$name}",
                'email'       => $email,
                'facility_id' => $facilityId,
                'role'        => 'manufacturer',
            ]);

            $this->counts['manufacturers']++;
        }

        $this->command?->info("   Done: {$this->counts['manufacturers']} manufacturers");
    }

    // =========================================================================
    // WHOLESALERS
    // =========================================================================
    private function seedWholesalers(): void
    {
        $this->command?->info('>> Seeding wholesale distributors...');
        $rows = $this->readCsv('PBB_Wholesale.csv');
        $rows = array_slice($rows, 0, 10);

        foreach ($rows as $i => $row) {
            $name    = $this->col($row, 'name')    ?? "Wholesaler " . ($i + 1);
            $licence = $this->col($row, 'licence') ?? 'WHL-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $county  = $this->col($row, 'county')  ?? 'Nairobi';
            $address = $this->col($row, 'address') ?? $county . ', Kenya';
            [$lat, $lng] = $this->resolveGps($row, $county);

            $slug  = Str::slug($name);
            $email = substr("wholesale.{$slug}@dawamtaani.test", 0, 80);

            $facilityId = $this->upsertFacility([
                'facility_name'      => $name,
                'ppb_licence_number' => $licence,
                'ppb_facility_type'  => 'WHOLESALE',
                'facility_status'    => 'ACTIVE',
                'county'             => $county,
                'physical_address'   => $address,
                'latitude'           => $lat,
                'longitude'          => $lng,
                'network_membership' => 'NETWORK',
                'email'              => $email,
                'owner_name'         => $this->col($row, 'owner') ?? $name,
            ]);

            $this->upsertUser([
                'name'        => $this->col($row, 'owner') ?? "Sales Manager - {$name}",
                'email'       => $email,
                'facility_id' => $facilityId,
                'role'        => 'wholesale_facility',
            ]);

            // Also create a warehouse_manager sub-user
            $wmEmail = substr("wm.{$slug}@dawamtaani.test", 0, 80);
            $this->upsertUser([
                'name'        => "Warehouse Mgr - {$name}",
                'email'       => $wmEmail,
                'facility_id' => $facilityId,
                'role'        => 'wholesale_facility',
            ]);

            $this->counts['wholesalers']++;
        }

        $this->command?->info("   Done: {$this->counts['wholesalers']} wholesalers");
    }

    // =========================================================================
    // RETAIL PHARMACIES
    // =========================================================================
    private function seedRetailers(): void
    {
        $this->command?->info('>> Seeding retail pharmacies...');
        $rows = $this->readCsv('PBB_Retail.csv');
        $rows = array_slice($rows, 0, 20);

        foreach ($rows as $i => $row) {
            $name    = $this->col($row, 'name')    ?? "Pharmacy " . ($i + 1);
            $licence = $this->col($row, 'licence') ?? 'RET-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $county  = $this->col($row, 'county')  ?? array_rand($this->countyCentroids);
            $address = $this->col($row, 'address') ?? $county . ', Kenya';
            [$lat, $lng] = $this->resolveGps($row, $county);

            $slug  = Str::slug($name);
            $email = substr("pharmacy.{$slug}@dawamtaani.test", 0, 80);

            $facilityId = $this->upsertFacility([
                'facility_name'      => $name,
                'ppb_licence_number' => $licence,
                'ppb_facility_type'  => 'RETAIL',
                'facility_status'    => 'ACTIVE',
                'county'             => $county,
                'sub_county'         => $this->col($row, 'sub_county'),
                'physical_address'   => $address,
                'latitude'           => $lat,
                'longitude'          => $lng,
                'network_membership' => ($i % 3 === 0) ? 'OFF_NETWORK' : 'NETWORK',
                'email'              => $email,
                'owner_name'         => $this->col($row, 'owner') ?? $name,
            ]);

            $this->upsertUser([
                'name'        => $this->col($row, 'owner') ?? "Pharmacist - {$name}",
                'email'       => $email,
                'facility_id' => $facilityId,
                'role'        => 'retail_facility',
            ]);

            $this->counts['retailers']++;
        }

        $this->command?->info("   Done: {$this->counts['retailers']} retail pharmacies");
    }

    // =========================================================================
    // HOSPITALS
    // =========================================================================
    private function seedHospitals(): void
    {
        $this->command?->info('>> Seeding hospitals (reference data)...');
        $rows = $this->readCsv('PPB_Hospitals.csv');
        $rows = array_slice($rows, 0, 10);

        foreach ($rows as $i => $row) {
            $name    = $this->col($row, 'name')    ?? "Hospital " . ($i + 1);
            $licence = $this->col($row, 'licence') ?? 'HSP-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $county  = $this->col($row, 'county')  ?? 'Nairobi';
            [$lat, $lng] = $this->resolveGps($row, $county);

            $this->upsertFacility([
                'facility_name'      => $name,
                'ppb_licence_number' => $licence,
                'ppb_facility_type'  => 'HOSPITAL',
                'facility_status'    => 'ACTIVE',
                'county'             => $county,
                'physical_address'   => $this->col($row, 'address') ?? $county,
                'latitude'           => $lat,
                'longitude'          => $lng,
                'network_membership' => 'NETWORK',
                'owner_name'         => $this->col($row, 'owner') ?? $name,
            ]);

            $this->counts['hospitals']++;
        }

        $this->command?->info("   Done: {$this->counts['hospitals']} hospitals");
    }

    // =========================================================================
    // PRODUCTS (50 SKUs)
    // =========================================================================
    private function seedProducts(): void
    {
        $this->command?->info('>> Seeding SKUs...');
        $rows = $this->readCsv('expanded_50_skus_seed.csv');

        foreach ($rows as $i => $row) {
            $skuCode   = $this->firstOf($row, ['sku_code','sku','code','Code','SKU','item_code','Item Code'])
                         ?? 'SKU-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $genName   = $this->firstOf($row, ['generic_name','name','product_name','Name','Product Name','drug_name','Drug Name'])
                         ?? "Product " . ($i + 1);
            $category  = $this->firstOf($row, ['therapeutic_category','category','Category','Therapeutic Category','class','Class'])
                         ?? 'General';
            $unitSize  = $this->firstOf($row, ['unit_size','unit','dosage_form','form','Unit','Dosage Form','Form','pack_size'])
                         ?? 'Tablet';
            $desc      = $this->firstOf($row, ['description','Description','indications','notes']) ?? null;

            $existing = DB::table('products')->where('sku_code', $skuCode)->first();

            if ($existing) {
                DB::table('products')->where('sku_code', $skuCode)->update([
                    'generic_name'          => $genName,
                    'therapeutic_category'  => $category,
                    'unit_size'             => $unitSize,
                    'description'           => $desc,
                    'is_active'             => true,
                    'updated_at'            => now(),
                ]);
            } else {
                DB::table('products')->insert([
                    'ulid'                  => Str::ulid()->toBase32(),
                    'sku_code'              => $skuCode,
                    'generic_name'          => $genName,
                    'therapeutic_category'  => $category,
                    'unit_size'             => $unitSize,
                    'description'           => $desc,
                    'is_active'             => true,
                    'created_by'            => 1,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            $this->counts['products']++;
        }

        $this->command?->info("   Done: {$this->counts['products']} products");
    }

    // =========================================================================
    // WHOLESALE PRICE LISTS
    // =========================================================================
    private function seedWholesalePriceLists(): void
    {
        $this->command?->info('>> Building wholesale price lists...');

        $wholesalers = DB::table('facilities')
            ->where('ppb_facility_type', 'WHOLESALE')
            ->where('facility_status', 'ACTIVE')
            ->get();

        $products = DB::table('products')
            ->where('is_active', true)
            ->get();

        if ($wholesalers->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('   No wholesalers or products found, skipping price lists.');
            return;
        }

        foreach ($wholesalers as $ws) {
            // Each wholesaler stocks 30-50 of the 50 products
            $assignedProducts = $products->shuffle()->take(rand(30, min(50, $products->count())));

            foreach ($assignedProducts as $product) {
                $basePrice = $this->skuBasePrices[$product->sku_code] ?? 100;
                // Small variance per wholesaler (+/-5%) to make data realistic
                $variance  = $basePrice * (rand(-5, 5) / 100);
                $listPrice = round($basePrice + $variance, 2);

                $existing = DB::table('wholesale_price_lists')
                    ->where('wholesale_facility_id', $ws->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$existing) {
                    DB::table('wholesale_price_lists')->insert([
                        'wholesale_facility_id' => $ws->id,
                        'product_id'            => $product->id,
                        'unit_price'            => $listPrice,
                        'effective_from'        => now()->startOfMonth()->toDateString(),
                        'expires_at'            => now()->addMonths(3)->endOfMonth()->toDateString(),
                        'stock_status'          => 'IN_STOCK',
                        'stock_quantity'        => rand(100, 1000),
                        'is_active'             => true,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                    $this->counts['price_lists']++;
                }
            }
        }

        $this->command?->info("   Done: {$this->counts['price_lists']} price list entries");
    }

    // =========================================================================
    // LINK RETAILERS <-> WHOLESALERS
    // =========================================================================
    private function linkRetailersToWholesalers(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('facility_wholesaler')) {
            $this->command?->warn('   No facility_wholesaler table found -- links skipped.');
            return;
        }

        $this->command?->info('>> Linking pharmacies to wholesalers...');

        $retailers   = DB::table('facilities')->where('ppb_facility_type', 'RETAIL')->get();
        $wholesalers = DB::table('facilities')->where('ppb_facility_type', 'WHOLESALE')->pluck('id')->toArray();

        if (empty($wholesalers)) return;

        $linked = 0;
        foreach ($retailers as $retail) {
            $count    = rand(1, min(3, count($wholesalers)));
            $assigned = array_slice($this->shuffleArray($wholesalers), 0, $count);

            foreach ($assigned as $idx => $wsId) {
                $exists = DB::table('facility_wholesaler')
                    ->where('retail_facility_id', $retail->id)
                    ->where('wholesale_facility_id', $wsId)
                    ->exists();

                if (!$exists) {
                    DB::table('facility_wholesaler')->insert([
                        'retail_facility_id'    => $retail->id,
                        'wholesale_facility_id' => $wsId,
                        'is_preferred'          => ($idx === 0),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                    $linked++;
                }
            }
        }

        $this->command?->info("   Done: {$linked} retailer->wholesaler links created");
    }

    // =========================================================================
    // HELPERS -- Facility & User upsert
    // =========================================================================

    private function upsertFacility(array $data): int
    {
        $existing = DB::table('facilities')
            ->where('ppb_licence_number', $data['ppb_licence_number'])
            ->first();

        $phone = $data['phone'] ?? '+25470000' . str_pad($this->phoneSuffix++, 4, '0', STR_PAD_LEFT);

        // Filter out null values from caller data so defaults aren't overridden
        $data = array_filter($data, fn ($v) => $v !== null);

        $payload = array_merge([
            'owner_name'         => $data['facility_name'] ?? 'N/A',
            'ppb_licence_status' => 'VALID',
            'onboarding_status'  => 'ACTIVE',
            'sub_county'         => 'N/A',
            'ward'               => 'N/A',
            'phone'              => $phone,
            'email'              => null,
            'network_membership' => 'NETWORK',
            'latitude'           => null,
            'longitude'          => null,
            'created_by'         => 1,
            'updated_at'         => now(),
        ], $data);

        // Remove keys not in schema
        unset($payload['phone_generated']);

        if ($existing) {
            // Don't overwrite phone on existing records (unique constraint)
            unset($payload['phone']);
            unset($payload['ppb_licence_number']); // can't update unique key
            unset($payload['created_by']);

            DB::table('facilities')
                ->where('id', $existing->id)
                ->update($payload);
            return $existing->id;
        }

        $id = DB::table('facilities')->insertGetId(array_merge($payload, [
            'ulid'       => Str::ulid()->toBase32(),
            'created_at' => now(),
        ]));

        return $id;
    }

    private function upsertUser(array $data): void
    {
        $existing = DB::table('users')->where('email', $data['email'])->first();

        if (!$existing) {
            $userId = DB::table('users')->insertGetId([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'email_verified_at' => now(),
                'password'          => $this->passwordHash,
                'facility_id'       => $data['facility_id'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } else {
            $userId = $existing->id;
            DB::table('users')->where('id', $userId)->update([
                'facility_id' => $data['facility_id'],
                'updated_at'  => now(),
            ]);
        }

        // Assign Spatie role
        try {
            $role = Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);
            $userModel = config('auth.providers.users.model', \App\Models\User::class);
            $user = $userModel::find($userId);
            if ($user && !$user->hasRole($data['role'])) {
                $user->assignRole($role);
            }
        } catch (\Throwable $e) {
            Log::warning("ComprehensiveDemoSeeder: role assignment failed for {$data['email']}: " . $e->getMessage());
        }

        $this->counts['users']++;
    }

    // =========================================================================
    // HELPERS -- CSV reading
    // =========================================================================

    private function readCsv(string $filename): array
    {
        $path = $this->dataDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($path)) {
            $this->command?->warn("   CSV not found: {$path} -- skipping");
            return [];
        }

        $handle  = fopen($path, 'r');
        $headers = null;
        $rows    = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                // Strip BOM from first header
                $line[0] = ltrim($line[0], "\xEF\xBB\xBF");
                $headers = array_map('trim', $line);
                continue;
            }
            // Pad short rows
            while (count($line) < count($headers)) {
                $line[] = null;
            }
            $rows[] = array_combine($headers, $line);
        }

        fclose($handle);
        return $rows;
    }

    private function col(array $row, string $canonical): ?string
    {
        $aliases = $this->colAliases[$canonical] ?? [$canonical];
        foreach ($aliases as $alias) {
            if (isset($row[$alias]) && $row[$alias] !== '' && $row[$alias] !== null) {
                return trim((string) $row[$alias]);
            }
        }
        return null;
    }

    private function firstOf(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                return trim((string) $row[$key]);
            }
        }
        return null;
    }

    private function resolveGps(array $row, string $county): array
    {
        $lat = $this->col($row, 'lat');
        $lng = $this->col($row, 'lng');

        if ($lat && $lng && is_numeric($lat) && is_numeric($lng)) {
            return [(float) $lat, (float) $lng];
        }

        $base = $this->countyCentroids[$county]
               ?? $this->countyCentroids['Nairobi'];

        return [
            round($base[0] + (rand(-500, 500) / 10000), 6),
            round($base[1] + (rand(-500, 500) / 10000), 6),
        ];
    }

    private function shuffleArray(array $arr): array
    {
        shuffle($arr);
        return $arr;
    }

    // =========================================================================
    // SUMMARY
    // =========================================================================
    private function printSummary(): void
    {
        $this->command?->info('');
        $this->command?->info('========================================');
        $this->command?->info('         Seeding Complete');
        $this->command?->info('========================================');
        $this->command?->info("  Manufacturers : {$this->counts['manufacturers']}");
        $this->command?->info("  Wholesalers   : {$this->counts['wholesalers']}");
        $this->command?->info("  Retailers     : {$this->counts['retailers']}");
        $this->command?->info("  Hospitals     : {$this->counts['hospitals']}");
        $this->command?->info("  Products      : {$this->counts['products']}");
        $this->command?->info("  Price Lists   : {$this->counts['price_lists']}");
        $this->command?->info("  Users created : {$this->counts['users']}");
        $this->command?->info('----------------------------------------');
        $this->command?->info('  All passwords : Password@123');
        $this->command?->info('  Email pattern : role.facility@dawamtaani.test');
        $this->command?->info('========================================');
        $this->command?->info('');
    }
}
