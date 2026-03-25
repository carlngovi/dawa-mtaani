<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure required system settings exist for tests
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'currency_symbol'],
            ['value' => 'KES', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'currency_decimal_places'],
            ['value' => '2', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'currency_iso_code'],
            ['value' => 'KES', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'search_cache_minutes'],
            ['value' => '15', 'created_at' => now(), 'updated_at' => now()]
        );

        Cache::flush();
    }

    private function createEligibleFacilityWithStock(array $facilityOverrides = []): array
    {
        $facility = Facility::factory()->create($facilityOverrides);

        $product = Product::factory()->create([
            'generic_name' => 'Amoxicillin 500mg',
            'brand_name'   => 'Amoxil',
        ]);

        DB::table('online_store_eligible_facilities')->insert([
            'facility_id'      => $facility->id,
            'qualified_at'     => now(),
            'pos_data_days'    => 120,
            'variance_score'   => 0.15,
            'branding_mode'    => $facilityOverrides['branding_mode'] ?? 'OWN_BRAND',
            'is_network_member' => ($facilityOverrides['network_membership'] ?? 'NETWORK') === 'NETWORK',
            'is_active'        => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('facility_stock_status')->insert([
            'wholesale_facility_id' => $facility->id,
            'product_id'            => $product->id,
            'stock_status'          => 'IN_STOCK',
            'stock_quantity'        => 500,
            'updated_by'            => 1,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        DB::table('wholesale_price_lists')->insert([
            'wholesale_facility_id' => $facility->id,
            'product_id'            => $product->id,
            'unit_price'            => 150.00,
            'effective_from'        => now()->subDay()->toDateString(),
            'is_active'             => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return [$facility, $product];
    }

    public function test_search_returns_empty_when_no_eligible_facilities(): void
    {
        $response = $this->getJson('/api/store/search?q=Amoxicillin');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data'   => [],
            ]);
    }

    public function test_search_returns_results_for_eligible_network_facility(): void
    {
        [$facility, $product] = $this->createEligibleFacilityWithStock();

        $response = $this->getJson('/api/store/search?q=Amoxicillin');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_ulid', $product->ulid)
            ->assertJsonPath('data.0.generic_name', 'Amoxicillin 500mg')
            ->assertJsonPath('data.0.verified_badge_eligible', true)
            ->assertJsonPath('data.0.display_name', $facility->facility_name);
    }

    public function test_verified_badge_is_false_for_off_network_facility(): void
    {
        $this->createEligibleFacilityWithStock([
            'network_membership' => 'OFF_NETWORK',
        ]);

        $response = $this->getJson('/api/store/search?q=Amoxicillin');

        $response->assertOk()
            ->assertJsonPath('data.0.verified_badge_eligible', false);
    }

    public function test_distance_km_present_with_coordinates_null_without(): void
    {
        $this->createEligibleFacilityWithStock([
            'latitude'  => -1.2921,
            'longitude' => 36.8219,
        ]);

        // With coordinates
        $response = $this->getJson('/api/store/search?q=Amoxicillin&lat=-1.3000&lng=36.8200');
        $response->assertOk();
        $data = $response->json('data.0');
        $this->assertNotNull($data['distance_km']);
        $this->assertIsFloat($data['distance_km'] + 0);

        // Without coordinates
        $response = $this->getJson('/api/store/search?q=Amoxicillin');
        $response->assertOk();
        $data = $response->json('data.0');
        $this->assertNull($data['distance_km']);
    }

    public function test_results_are_cached_on_second_call(): void
    {
        $this->createEligibleFacilityWithStock();

        // First call — populates cache
        $response1 = $this->getJson('/api/store/search?q=Amoxicillin');
        $response1->assertOk()->assertJsonCount(1, 'data');

        // Delete the eligible facility row so DB would return empty
        DB::table('online_store_eligible_facilities')->delete();

        // Second call — should still return cached result
        $response2 = $this->getJson('/api/store/search?q=Amoxicillin');
        $response2->assertOk()->assertJsonCount(1, 'data');
    }
}
