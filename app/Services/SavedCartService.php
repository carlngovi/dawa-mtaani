<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavedCartService
{
    public function __construct(
        private readonly PricingEngine $pricingEngine
    ) {}

    public function load(string $cartUlid, int $targetFacilityId): array
    {
        $cart = DB::table('saved_carts')
            ->where('ulid', $cartUlid)
            ->first();

        if (! $cart) {
            return ['found' => false, 'message' => 'Cart not found.'];
        }

        $lines = DB::table('saved_cart_lines as scl')
            ->join('products as p', 'scl.product_id', '=', 'p.id')
            ->join('facilities as wf', 'scl.wholesale_facility_id', '=', 'wf.id')
            ->where('scl.saved_cart_id', $cart->id)
            ->select([
                'scl.id',
                'scl.product_id',
                'scl.wholesale_facility_id',
                'scl.quantity',
                'p.generic_name',
                'p.brand_name',
                'p.sku_code',
                'p.unit_size',
                'wf.facility_name as wholesale_facility_name',
            ])
            ->get();

        // Check if facility is off-network
        $isOffNetwork = $this->pricingEngine->isOffNetwork($targetFacilityId);

        $refreshedLines = [];
        $unavailableLines = [];

        foreach ($lines as $line) {
            // Fetch LIVE price from active price list — never use saved price
            $priceList = DB::table('wholesale_price_lists')
                ->where('wholesale_facility_id', $line->wholesale_facility_id)
                ->where('product_id', $line->product_id)
                ->where('is_active', true)
                ->where('effective_from', '<=', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now()->toDateString());
                })
                ->orderBy('effective_from', 'desc')
                ->first();

            if (! $priceList) {
                $unavailableLines[] = [
                    'product_id'   => $line->product_id,
                    'generic_name' => $line->generic_name,
                    'reason'       => 'No active price list found for this product.',
                ];
                continue;
            }

            // Apply off-network premium if applicable
            $liveUnitPrice = (float) $priceList->unit_price;
            $premiumApplied = false;
            $premiumAmount = 0.0;

            if ($isOffNetwork) {
                $finalPrice = $this->pricingEngine->applyAgreement(
                    $targetFacilityId,
                    $liveUnitPrice
                );
                $premiumAmount = round($finalPrice - $liveUnitPrice, 2);
                $premiumApplied = $premiumAmount > 0;
                $liveUnitPrice = $finalPrice;
            }

            $refreshedLines[] = [
                'saved_cart_line_id'      => $line->id,
                'product_id'              => $line->product_id,
                'wholesale_facility_id'   => $line->wholesale_facility_id,
                'wholesale_facility_name' => $line->wholesale_facility_name,
                'price_list_id'           => $priceList->id,
                'generic_name'            => $line->generic_name,
                'brand_name'              => $line->brand_name,
                'sku_code'                => $line->sku_code,
                'unit_size'               => $line->unit_size,
                'quantity'                => $line->quantity,
                'unit_price'              => $liveUnitPrice,
                'premium_applied'         => $premiumApplied,
                'premium_amount'          => $premiumAmount,
                'line_total'              => round($liveUnitPrice * $line->quantity, 2),
                'stock_status'            => $priceList->stock_status,
            ];
        }

        return [
            'found'              => true,
            'cart'               => $cart,
            'lines'              => $refreshedLines,
            'unavailable_lines'  => $unavailableLines,
            'is_off_network'     => $isOffNetwork,
            'pricing_refreshed_at' => now('UTC')->toISOString(),
        ];
    }

    public function create(
        string $name,
        int $createdBy,
        ?int $ownerFacilityId = null,
        ?int $ownerGroupId = null,
        bool $isGroupCart = false
    ): string {
        $ulid = \Illuminate\Support\Str::ulid();

        DB::table('saved_carts')->insert([
            'ulid'              => $ulid,
            'name'              => $name,
            'owner_facility_id' => $ownerFacilityId,
            'owner_group_id'    => $ownerGroupId,
            'is_group_cart'     => $isGroupCart,
            'created_by'        => $createdBy,
            'created_at'        => now('UTC'),
            'updated_at'        => now('UTC'),
        ]);

        return $ulid;
    }

    public function createConflictDraft(
        int $originalOrderId,
        int $facilityId,
        int $createdBy,
        array $conflictLines
    ): string {
        $ulid = \Illuminate\Support\Str::ulid();

        $cartId = DB::table('saved_carts')->insertGetId([
            'ulid'                      => $ulid,
            'name'                      => 'Conflict Draft - ' . now()->format('Y-m-d H:i'),
            'owner_facility_id'         => $facilityId,
            'is_group_cart'             => false,
            'conflict_source_order_id'  => $originalOrderId,
            'conflict_resolution_status' => 'PENDING',
            'created_by'                => $createdBy,
            'created_at'                => now('UTC'),
            'updated_at'                => now('UTC'),
        ]);

        foreach ($conflictLines as $line) {
            DB::table('saved_cart_lines')->insert([
                'saved_cart_id'         => $cartId,
                'product_id'            => $line['product_id'],
                'wholesale_facility_id' => $line['wholesale_facility_id'],
                'quantity'              => $line['quantity'],
                'created_at'            => now('UTC'),
                'updated_at'            => now('UTC'),
            ]);
        }

        return $ulid;
    }
}
