<?php

namespace Tests\Feature;

use App\Jobs\NotifyPpbOfCounterfeitJob;
use App\Models\Facility;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CounterfeitReportedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupplyChainBadgeTest extends TestCase
{
    use DatabaseTransactions;

    private function createNetworkFacility(array $overrides = []): Facility
    {
        return Facility::factory()->create(array_merge([
            'network_membership' => 'NETWORK',
            'ppb_licence_status' => 'VALID',
        ], $overrides));
    }

    private function createOffNetworkFacility(): Facility
    {
        return Facility::factory()->create([
            'network_membership' => 'OFF_NETWORK',
        ]);
    }

    private function createProduct(): Product
    {
        return Product::factory()->create();
    }

    private function seedAllFourEvents(Facility $facility, Product $product): void
    {
        // A wholesale supplier facility
        $supplier = Facility::factory()->create([
            'facility_name'      => 'Trusted Supplier Ltd',
            'ppb_licence_number' => 'PPB-SUP-001',
        ]);

        // EVENT 1 — Supplier identity (active wholesale price list)
        DB::table('wholesale_price_lists')->insert([
            'wholesale_facility_id' => $supplier->id,
            'product_id'            => $product->id,
            'unit_price'            => 100.00,
            'effective_from'        => now()->subDays(30)->toDateString(),
            'is_active'             => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Create an order with DELIVERED status
        $orderId = DB::table('orders')->insertGetId([
            'ulid'                => strtolower(Str::ulid()),
            'retail_facility_id'  => $facility->id,
            'placed_by_user_id'   => User::factory()->create(['facility_id' => $facility->id])->id,
            'is_network_member'   => true,
            'order_type'          => 'CASH',
            'status'              => 'DELIVERED',
            'total_amount'        => 100.00,
            'submitted_at'        => now()->subDays(5),
            'confirmed_at'        => now()->subDays(4),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // EVENT 3 — Order line delivered
        DB::table('order_lines')->insert([
            'order_id'              => $orderId,
            'wholesale_facility_id' => $supplier->id,
            'product_id'            => $product->id,
            'price_list_id'         => DB::table('wholesale_price_lists')->where('product_id', $product->id)->value('id'),
            'quantity'              => 10,
            'unit_price'            => 100.00,
            'line_total'            => 1000.00,
            'payment_type'          => 'CASH',
            'placer_user_id'        => DB::table('orders')->where('id', $orderId)->value('placed_by_user_id'),
            'delivery_facility_id'  => $facility->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // EVENT 2 — Delivery confirmation
        $confirmer = User::factory()->create(['name' => 'Jane Pharmacist', 'facility_id' => $facility->id]);
        DB::table('delivery_confirmations')->insert([
            'order_id'                      => $orderId,
            'logistics_facility_id'         => $supplier->id,
            'delivered_at'                  => now()->subDays(3),
            'pod_photo_path'                => '/storage/pod/test.jpg',
            'confirmation_clock_started_at' => now()->subDays(3),
            'confirmation_deadline_at'      => now()->subDays(0),
            'confirmed_at'                  => now()->subDays(2),
            'confirmed_by'                  => $confirmer->id,
            'confirmation_type'             => 'RETAIL_CONFIRMED',
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        // EVENT 4 — Dispensing record
        DB::table('dispensing_entries')->insert([
            'facility_id'  => $facility->id,
            'product_id'   => $product->id,
            'quantity'      => 5,
            'dispensed_at'  => now()->subDay(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function test_off_network_facility_returns_verified_false(): void
    {
        $facility = $this->createOffNetworkFacility();
        $product = $this->createProduct();

        $response = $this->getJson("/api/store/products/{$product->id}/custody-chain?facility_id={$facility->id}");

        $response->assertOk()
            ->assertJsonPath('data.verified', false);

        // Should NOT contain chain data
        $this->assertArrayNotHasKey('chain', $response->json('data'));
    }

    public function test_network_facility_with_all_events_returns_verified_true(): void
    {
        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();
        $this->seedAllFourEvents($facility, $product);

        $response = $this->getJson("/api/store/products/{$product->id}/custody-chain?facility_id={$facility->id}");

        $response->assertOk()
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.facility_ppb_status', 'VALID')
            ->assertJsonCount(4, 'data.chain');

        $chain = $response->json('data.chain');
        $this->assertEquals('sourced', $chain[0]['event']);
        $this->assertEquals('delivered', $chain[1]['event']);
        $this->assertEquals('received', $chain[2]['event']);
        $this->assertEquals('dispensed', $chain[3]['event']);
    }

    public function test_missing_delivery_confirmation_returns_verified_false(): void
    {
        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();

        // Seed events 1, 3, 4 but NOT event 2 (delivery confirmation)
        $supplier = Facility::factory()->create();

        // EVENT 1
        DB::table('wholesale_price_lists')->insert([
            'wholesale_facility_id' => $supplier->id,
            'product_id'            => $product->id,
            'unit_price'            => 100.00,
            'effective_from'        => now()->subDays(30)->toDateString(),
            'is_active'             => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // EVENT 4
        DB::table('dispensing_entries')->insert([
            'facility_id'  => $facility->id,
            'product_id'   => $product->id,
            'quantity'      => 5,
            'dispensed_at'  => now()->subDay(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // No delivery confirmation or delivered order line

        $response = $this->getJson("/api/store/products/{$product->id}/custody-chain?facility_id={$facility->id}");

        $response->assertOk()
            ->assertJsonPath('data.verified', false);

        $this->assertArrayNotHasKey('chain', $response->json('data'));
    }

    public function test_missing_dispensing_entry_returns_verified_false(): void
    {
        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();

        $supplier = Facility::factory()->create();

        // EVENT 1
        DB::table('wholesale_price_lists')->insert([
            'wholesale_facility_id' => $supplier->id,
            'product_id'            => $product->id,
            'unit_price'            => 100.00,
            'effective_from'        => now()->subDays(30)->toDateString(),
            'is_active'             => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $user = User::factory()->create(['facility_id' => $facility->id]);

        $orderId = DB::table('orders')->insertGetId([
            'ulid'                => strtolower(Str::ulid()),
            'retail_facility_id'  => $facility->id,
            'placed_by_user_id'   => $user->id,
            'is_network_member'   => true,
            'order_type'          => 'CASH',
            'status'              => 'DELIVERED',
            'total_amount'        => 100.00,
            'submitted_at'        => now()->subDays(5),
            'confirmed_at'        => now()->subDays(4),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // EVENT 3
        DB::table('order_lines')->insert([
            'order_id'              => $orderId,
            'wholesale_facility_id' => $supplier->id,
            'product_id'            => $product->id,
            'price_list_id'         => DB::table('wholesale_price_lists')->where('product_id', $product->id)->value('id'),
            'quantity'              => 10,
            'unit_price'            => 100.00,
            'line_total'            => 1000.00,
            'payment_type'          => 'CASH',
            'placer_user_id'        => $user->id,
            'delivery_facility_id'  => $facility->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // EVENT 2
        DB::table('delivery_confirmations')->insert([
            'order_id'                      => $orderId,
            'logistics_facility_id'         => $supplier->id,
            'delivered_at'                  => now()->subDays(3),
            'pod_photo_path'                => '/storage/pod/test.jpg',
            'confirmation_clock_started_at' => now()->subDays(3),
            'confirmation_deadline_at'      => now(),
            'confirmed_at'                  => now()->subDays(2),
            'confirmed_by'                  => $user->id,
            'confirmation_type'             => 'RETAIL_CONFIRMED',
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        // NO EVENT 4 — dispensing entry missing

        $response = $this->getJson("/api/store/products/{$product->id}/custody-chain?facility_id={$facility->id}");

        $response->assertOk()
            ->assertJsonPath('data.verified', false);

        $this->assertArrayNotHasKey('chain', $response->json('data'));
    }

    public function test_counterfeit_report_stores_phone_as_hash(): void
    {
        Notification::fake();

        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();

        $response = $this->postJson('/api/store/counterfeit-reports', [
            'facility_id'   => $facility->id,
            'product_id'    => $product->id,
            'patient_phone' => '+254712345678',
            'report_notes'  => 'Suspicious packaging',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $report = DB::table('patient_counterfeit_reports')->first();
        $this->assertNotNull($report);
        // Phone should be hashed, NOT stored as plain text
        $this->assertNotEquals('+254712345678', $report->patient_phone);
        $this->assertTrue(Hash::check('+254712345678', $report->patient_phone));
    }

    public function test_counterfeit_report_notifies_network_admin_users(): void
    {
        Notification::fake();

        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();

        // Create a network_admin user
        $admin = User::factory()->create();
        $admin->assignRole(
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'network_admin', 'guard_name' => 'web'])
        );

        $response = $this->postJson('/api/store/counterfeit-reports', [
            'facility_id'   => $facility->id,
            'product_id'    => $product->id,
            'patient_phone' => '+254700000000',
        ]);

        $response->assertOk();

        Notification::assertSentTo($admin, CounterfeitReportedNotification::class);
    }

    public function test_counterfeit_report_dispatches_ppb_job(): void
    {
        Queue::fake();
        Notification::fake();

        $facility = $this->createNetworkFacility();
        $product = $this->createProduct();

        $response = $this->postJson('/api/store/counterfeit-reports', [
            'facility_id'   => $facility->id,
            'product_id'    => $product->id,
            'patient_phone' => '+254700000000',
        ]);

        $response->assertOk();

        Queue::assertPushedOn('quality-flags', NotifyPpbOfCounterfeitJob::class);
    }
}
