<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Product;
use App\Models\User;
use App\Models\WholesalePriceList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAndPriceSeeder extends Seeder
{
    public function run(): void
    {
        // ── PART 1: Seed products from CSV ──────────────────────────────
        $csvPath = database_path('seeders/data/expanded_50_skus_seed.csv');
        if (! file_exists($csvPath)) {
            $this->command->error("CSV not found at {$csvPath}");
            return;
        }

        $raw = file_get_contents($csvPath);
        // Strip BOM
        $raw = ltrim($raw, "\xEF\xBB\xBF");
        $lines = explode("\n", str_replace("\r\n", "\n", $raw));

        // Skip header
        array_shift($lines);

        $seeded = 0;
        $categories = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = str_getcsv($line);
            if (count($row) < 5) {
                continue;
            }

            [$skuCode, $genericName, $category, $unitSize, $description] = array_map('trim', $row);

            $product = Product::updateOrCreate(
                ['sku_code' => $skuCode],
                [
                    'ulid'                  => Product::where('sku_code', $skuCode)->value('ulid') ?? Str::ulid()->toBase32(),
                    'generic_name'          => $genericName,
                    'therapeutic_category'   => $category,
                    'unit_size'             => $unitSize,
                    'description'           => $description,
                    'is_active'             => true,
                    'created_by'            => 1,
                ]
            );

            $categories[$category] = ($categories[$category] ?? 0) + 1;
            $seeded++;
        }

        $this->command->info("Seeded {$seeded} products across " . count($categories) . " therapeutic categories");

        // ── PART 2: Wholesale facility ──────────────────────────────────
        $facility = Facility::firstOrCreate(
            ['ppb_licence_number' => 'PPB-WHOLESALE-001'],
            [
                'ulid'               => Str::ulid()->toBase32(),
                'ppb_facility_type'  => 'WHOLESALE',
                'ppb_licence_status' => 'VALID',
                'facility_name'      => 'Dawa Wholesale Distribution Centre',
                'owner_name'         => 'Dawa Wholesale Ltd',
                'phone'              => '+254700000010',
                'email'              => 'wholesale.facility@test.com',
                'county'             => 'Nairobi',
                'sub_county'         => 'Westlands',
                'ward'               => 'Parklands',
                'physical_address'   => 'Westlands Commercial Centre, Nairobi',
                'network_membership' => 'NETWORK',
                'facility_status'    => 'ACTIVE',
                'onboarding_status'  => 'ACTIVE',
                'created_by'         => 1,
            ]
        );

        $this->command->info("Wholesale facility: {$facility->facility_name} (ID: {$facility->id})");

        // ── PART 3: Price lists ─────────────────────────────────────────
        $skuPrices = [
            // Anti-Malarials
            'MAL-001' => 165, 'MAL-002' => 95, 'MAL-003' => 55,
            'MAL-004' => 120, 'MAL-005' => 280,
            // Antibiotics - Access (flat 180)
            'ANT-ACC-001' => 180, 'ANT-ACC-002' => 180, 'ANT-ACC-003' => 180,
            'ANT-ACC-004' => 180, 'ANT-ACC-005' => 180, 'ANT-ACC-006' => 180,
            'ANT-ACC-007' => 180,
            // Antibiotics - Watch (flat 320)
            'ANT-WCH-001' => 320, 'ANT-WCH-002' => 320,
            // Reproductive Health
            'REP-001' => 95, 'REP-002' => 220, 'REP-003' => 210,
            'REP-004' => 380, 'REP-005' => 85,
            // Barrier Contraception
            'BAR-001' => 45,
            // Chronic Disease
            'CHR-001' => 85, 'CHR-002' => 95, 'CHR-003' => 120,
            'CHR-004' => 145, 'CHR-005' => 110, 'CHR-006' => 65,
            // Pediatric Health
            'PED-001' => 35, 'PED-002' => 45, 'PED-003' => 75,
            // Vitamins & Minerals
            'VIT-001' => 95, 'VIT-002' => 65, 'VIT-003' => 120,
            // Pain & Allergy
            'PAI-001' => 55, 'PAI-002' => 85, 'PAI-003' => 35, 'PAI-004' => 75,
            // Gastrointestinal
            'GAS-001' => 145, 'GAS-002' => 85, 'GAS-003' => 35,
            // Deworming
            'DEW-001' => 25,
            // HIV Support
            'HIV-001' => 180, 'HIV-002' => 95,
            // Respiratory
            'RES-001' => 420, 'RES-002' => 55,
            // Antifungal
            'FUN-001' => 195, 'FUN-002' => 85,
            // Topical/Wound Care
            'TOP-001' => 95,
            // Diagnostics
            'DIA-001' => 850, 'DIA-002' => 1200,
        ];

        $products = Product::all();
        $priceCount = 0;

        foreach ($products as $product) {
            $price = $skuPrices[$product->sku_code] ?? 100; // fallback

            // Stock quantity by type
            if (in_array($product->sku_code, ['DIA-001', 'DIA-002'])) {
                $qty = rand(20, 100);
            } elseif ($product->sku_code === 'RES-001') {
                $qty = rand(30, 80);
            } elseif ($product->sku_code === 'REP-004') {
                $qty = rand(20, 60);
            } else {
                $qty = rand(100, 1000);
            }

            WholesalePriceList::updateOrCreate(
                [
                    'wholesale_facility_id' => $facility->id,
                    'product_id'            => $product->id,
                ],
                [
                    'unit_price'     => $price,
                    'effective_from' => now()->toDateString(),
                    'expires_at'     => null,
                    'stock_status'   => 'IN_STOCK',
                    'stock_quantity' => $qty,
                    'is_active'      => true,
                ]
            );

            $priceCount++;
        }

        $this->command->info("Created {$priceCount} price list entries");

        // ── PART 4: Link wholesale user to facility ─────────────────────
        $user = User::where('email', 'wholesale.facility@test.com')->first();
        if ($user && ! $user->facility_id) {
            $user->update(['facility_id' => $facility->id]);
            $this->command->info("Linked user {$user->email} to facility ID {$facility->id}");
        }

        // ── PART 5: Summary ─────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('=== ProductAndPriceSeeder Complete ===');
        $this->command->info('Products: ' . Product::count());
        $this->command->info('Price lists: ' . WholesalePriceList::count());
        $this->command->info("Wholesale facility: {$facility->facility_name} (ID: {$facility->id})");
        $this->command->info('Price range: KES ' . WholesalePriceList::min('unit_price') . ' — KES ' . WholesalePriceList::max('unit_price'));

        $cheapest = WholesalePriceList::with('product')->orderBy('unit_price')->first();
        $priciest = WholesalePriceList::with('product')->orderByDesc('unit_price')->first();
        $this->command->info('Cheapest: ' . ($cheapest?->product?->generic_name ?? 'N/A'));
        $this->command->info('Most expensive: ' . ($priciest?->product?->generic_name ?? 'N/A'));
    }
}
