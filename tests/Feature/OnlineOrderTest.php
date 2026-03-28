<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\CustomerBasket;
use App\Models\CustomerBasketLine;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\Integrations\MpesaDarajaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OnlineOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

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
            ['key' => 'reservation_minutes'],
            ['value' => '15', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'platform_fee_pct'],
            ['value' => '3.00', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'partial_fulfilment'],
            ['value' => 'true', 'created_at' => now(), 'updated_at' => now()]
        );

        Cache::flush();
    }

    private function createFacilityWithStock(int $stockQty = 500): array
    {
        $facility = Facility::factory()->create();
        $product = Product::factory()->create([
            'generic_name' => 'Amoxicillin 500mg',
            'brand_name'   => 'Amoxil',
        ]);

        DB::table('facility_stock_status')->insert([
            'wholesale_facility_id' => $facility->id,
            'product_id'            => $product->id,
            'stock_status'          => 'IN_STOCK',
            'stock_quantity'        => $stockQty,
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

    private function createBasketWithItem(Facility $facility, Product $product, int $qty = 2): CustomerBasket
    {
        $basket = CustomerBasket::create([
            'customer_phone' => '+254712345678',
            'facility_id'    => $facility->id,
            'session_token'  => 'test-token-' . uniqid(),
        ]);

        CustomerBasketLine::create([
            'basket_id'  => $basket->id,
            'product_id' => $product->id,
            'quantity'   => $qty,
            'added_at'   => now(),
        ]);

        return $basket;
    }

    public function test_add_item_creates_basket_and_basket_line(): void
    {
        [$facility, $product] = $this->createFacilityWithStock();

        $response = $this->postJson('/api/store/basket/add', [
            'customer_phone' => '+254712345678',
            'facility_id'    => $facility->id,
            'product_id'     => $product->id,
            'quantity'       => 3,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['basket_token']]);

        $this->assertDatabaseHas('customer_baskets', [
            'customer_phone' => '+254712345678',
            'facility_id'    => $facility->id,
        ]);

        $basket = CustomerBasket::where('customer_phone', '+254712345678')->first();
        $this->assertDatabaseHas('customer_basket_lines', [
            'basket_id'  => $basket->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);
    }

    public function test_reserve_locks_stock_and_sets_reserved_until(): void
    {
        [$facility, $product] = $this->createFacilityWithStock(500);
        $basket = $this->createBasketWithItem($facility, $product, 2);

        $response = $this->postJson('/api/store/basket/reserve', [
            'session_token' => $basket->session_token,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['reserved_until']]);

        $basket->refresh();
        $this->assertNotNull($basket->reserved_until);
        $this->assertTrue($basket->reserved_until->isFuture());
    }

    public function test_reserve_returns_unavailable_items_when_stock_insufficient(): void
    {
        [$facility, $product] = $this->createFacilityWithStock(1);
        $basket = $this->createBasketWithItem($facility, $product, 10);

        $response = $this->postJson('/api/store/basket/reserve', [
            'session_token' => $basket->session_token,
        ]);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data['unavailable_items']);
        $this->assertEquals('Amoxicillin 500mg', $data['unavailable_items'][0]['product_name']);
        $this->assertEquals(10, $data['unavailable_items'][0]['requested_qty']);
        $this->assertEquals(1, $data['unavailable_items'][0]['available_qty']);
    }

    public function test_checkout_creates_order_with_correct_totals(): void
    {
        [$facility, $product] = $this->createFacilityWithStock();
        $basket = $this->createBasketWithItem($facility, $product, 2);
        $basket->update(['reserved_until' => now()->addMinutes(15)]);

        $this->mock(MpesaDarajaService::class, function ($mock) {
            $mock->shouldReceive('initiateSTKPush')
                ->once()
                ->andReturn(['CheckoutRequestID' => 'ws_CO_TEST123']);
        });

        $response = $this->postJson('/api/store/orders/checkout', [
            'session_token'  => $basket->session_token,
            'customer_phone' => '+254712345678',
            'customer_name'  => 'Test Customer',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['order_ulid', 'total', 'mpesa_prompt']]);

        $order = CustomerOrder::where('ulid', $response->json('data.order_ulid'))->first();
        $this->assertNotNull($order);
        $this->assertEquals('PAYMENT_PENDING', $order->status);
        // 150 * 2 = 300 subtotal
        $this->assertEquals(300.00, (float) $order->subtotal_amount);
        $this->assertEquals(300.00, (float) $order->total_amount);
        // Platform fee: 300 * 3% = 9.00
        $this->assertEquals(9.00, (float) $order->platform_fee_amount);
        $this->assertEquals(291.00, (float) $order->facility_net_amount);
        $this->assertEquals('ws_CO_TEST123', $order->mpesa_checkout_request_id);
    }

    public function test_mpesa_callback_confirms_order_and_decrements_stock(): void
    {
        [$facility, $product] = $this->createFacilityWithStock(500);

        $order = CustomerOrder::create([
            'ulid'                      => '01htest000000000000000001',
            'customer_phone'            => '+254712345678',
            'facility_id'               => $facility->id,
            'status'                    => 'PAYMENT_PENDING',
            'subtotal_amount'           => 300,
            'discount_amount'           => 0,
            'total_amount'              => 300,
            'platform_fee_pct'          => 3.00,
            'platform_fee_amount'       => 9.00,
            'facility_net_amount'       => 291.00,
            'mpesa_checkout_request_id' => 'ws_CO_TEST456',
        ]);

        $order->lines()->create([
            'product_id'    => $product->id,
            'quantity'      => 2,
            'unit_price'    => 150.00,
            'line_discount' => 0,
            'line_total'    => 300.00,
        ]);

        $response = $this->postJson('/api/store/orders/mpesa-callback', [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_TEST456',
                    'ResultCode'        => 0,
                    'CallbackMetadata'  => [
                        'Item' => [
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'REC123ABC'],
                            ['Name' => 'Amount', 'Value' => 300],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok');

        $order->refresh();
        $this->assertEquals('CONFIRMED', $order->status);
        $this->assertEquals('REC123ABC', $order->mpesa_receipt_number);
        $this->assertNotNull($order->paid_at);

        $stock = DB::table('facility_stock_status')
            ->where('wholesale_facility_id', $facility->id)
            ->where('product_id', $product->id)
            ->value('stock_quantity');
        $this->assertEquals(498, $stock);
    }

    public function test_duplicate_mpesa_callback_returns_already_processed(): void
    {
        [$facility, $product] = $this->createFacilityWithStock(500);

        $order = CustomerOrder::create([
            'ulid'                      => '01htest000000000000000002',
            'customer_phone'            => '+254712345678',
            'facility_id'               => $facility->id,
            'status'                    => 'CONFIRMED',
            'subtotal_amount'           => 300,
            'discount_amount'           => 0,
            'total_amount'              => 300,
            'platform_fee_pct'          => 3.00,
            'platform_fee_amount'       => 9.00,
            'facility_net_amount'       => 291.00,
            'mpesa_checkout_request_id' => 'ws_CO_TEST789',
            'mpesa_receipt_number'      => 'REC456DEF',
            'paid_at'                   => now(),
        ]);

        $order->lines()->create([
            'product_id'    => $product->id,
            'quantity'      => 2,
            'unit_price'    => 150.00,
            'line_discount' => 0,
            'line_total'    => 300.00,
        ]);

        $response = $this->postJson('/api/store/orders/mpesa-callback', [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_TEST789',
                    'ResultCode'        => 0,
                    'CallbackMetadata'  => [
                        'Item' => [
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'REC456DEF'],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'already_processed');

        // Stock not decremented
        $stock = DB::table('facility_stock_status')
            ->where('wholesale_facility_id', $facility->id)
            ->where('product_id', $product->id)
            ->value('stock_quantity');
        $this->assertEquals(500, $stock);
    }

    public function test_checkout_with_expired_reservation_returns_422(): void
    {
        [$facility, $product] = $this->createFacilityWithStock();
        $basket = $this->createBasketWithItem($facility, $product);
        $basket->update(['reserved_until' => now()->subMinutes(5)]);

        $response = $this->postJson('/api/store/orders/checkout', [
            'session_token'  => $basket->session_token,
            'customer_phone' => '+254712345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Reservation expired. Please restart checkout.');
    }

    public function test_percentage_off_promo_applies_correct_discount(): void
    {
        [$facility, $product] = $this->createFacilityWithStock();
        $basket = $this->createBasketWithItem($facility, $product, 2);
        $basket->update(['reserved_until' => now()->addMinutes(15)]);

        $user = \App\Models\User::factory()->create();

        PromoCode::create([
            'code'           => 'SAVE10',
            'discount_type'  => 'PERCENTAGE_OFF',
            'discount_value' => 10.00,
            'valid_from'     => now()->subDay(),
            'valid_until'    => now()->addDay(),
            'created_by'     => $user->id,
        ]);

        $this->mock(MpesaDarajaService::class, function ($mock) {
            $mock->shouldReceive('initiateSTKPush')
                ->once()
                ->andReturn(['CheckoutRequestID' => 'ws_CO_PROMO123']);
        });

        $response = $this->postJson('/api/store/orders/checkout', [
            'session_token'  => $basket->session_token,
            'customer_phone' => '+254712345678',
            'promo_code'     => 'SAVE10',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $order = CustomerOrder::where('ulid', $response->json('data.order_ulid'))->first();
        // Subtotal: 150 * 2 = 300, Discount: 10% = 30, Total: 270
        $this->assertEquals(300.00, (float) $order->subtotal_amount);
        $this->assertEquals(30.00, (float) $order->discount_amount);
        $this->assertEquals(270.00, (float) $order->total_amount);
    }
}
