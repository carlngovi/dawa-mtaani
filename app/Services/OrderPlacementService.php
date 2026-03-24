<?php

namespace App\Services;

use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderPlacementService
{
    public function __construct(
        private readonly PricingEngine $pricingEngine
    ) {}

    public function placeOrder(
        int $facilityId,
        int $placedByUserId,
        array $lines,
        string $orderType,
        string $sourceChannel = 'WEB',
        bool $isGroupOrder = false,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use (
            $facilityId, $placedByUserId, $lines,
            $orderType, $sourceChannel, $isGroupOrder, $notes
        ) {
            // STEP 1 — Verify authorised placer
            $isAuthorised = DB::table('facility_authorised_placers')
                ->where('facility_id', $facilityId)
                ->where('user_id', $placedByUserId)
                ->where('is_active', true)
                ->exists();

            if (! $isAuthorised) {
                throw new \Illuminate\Auth\Access\AuthorizationException(
                    'You are not an authorised order placer for this facility.'
                );
            }

            // STEP 2 — Get facility details
            $facility = DB::table('facilities')
                ->where('id', $facilityId)
                ->lockForUpdate()
                ->first();

            if (! $facility) {
                throw new \InvalidArgumentException('Facility not found.');
            }

            $isOffNetwork = $facility->network_membership === 'OFF_NETWORK';
            $isNetworkMember = ! $isOffNetwork;

            // STEP 3 — Load and price each line
            $processedLines = [];
            $totalAmount = 0.0;
            $creditAmount = 0.0;
            $cashAmount = 0.0;

            foreach ($lines as $lineData) {
                $priceList = DB::table('wholesale_price_lists')
                    ->where('id', $lineData['price_list_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $priceList) {
                    throw new \InvalidArgumentException(
                        "Price list not found for product ID: {$lineData['product_id']}"
                    );
                }

                $unitPrice = (float) $priceList->unit_price;
                $premiumApplied = false;
                $premiumAmount = 0.0;

                // Apply off-network premium server-side
                if ($isOffNetwork) {
                    $finalPrice = $this->pricingEngine->applyAgreement($facilityId, $unitPrice);
                    $premiumAmount = round($finalPrice - $unitPrice, 2);
                    $premiumApplied = $premiumAmount > 0;
                    $unitPrice = $finalPrice;
                }

                $lineTotal = round($unitPrice * $lineData['quantity'], 2);
                $totalAmount += $lineTotal;

                $paymentType = $isOffNetwork ? 'OFF_NETWORK_CASH' : ($lineData['payment_type'] ?? 'CASH');

                if ($paymentType === 'CREDIT') {
                    $creditAmount += $lineTotal;
                } else {
                    $cashAmount += $lineTotal;
                }

                $processedLines[] = [
                    'wholesale_facility_id'  => $priceList->wholesale_facility_id,
                    'product_id'             => $lineData['product_id'],
                    'price_list_id'          => $priceList->id,
                    'quantity'               => $lineData['quantity'],
                    'unit_price'             => $unitPrice,
                    'premium_applied'        => $premiumApplied,
                    'premium_amount'         => $premiumAmount,
                    'line_total'             => $lineTotal,
                    'payment_type'           => $paymentType,
                    'tranche_id'             => $lineData['tranche_id'] ?? null,
                    'tier_id'                => $lineData['tier_id'] ?? null,
                    'placer_user_id'         => $placedByUserId,
                    'delivery_facility_id'   => $lineData['delivery_facility_id'] ?? null,
                ];
            }

            // STEP 4 — Create order record
            $ulid = (string) Str::ulid();
            $now = Carbon::now('UTC');

            $orderId = DB::table('orders')->insertGetId([
                'ulid'                => $ulid,
                'retail_facility_id'  => $facilityId,
                'placed_by_user_id'   => $placedByUserId,
                'is_group_order'      => $isGroupOrder,
                'is_network_member'   => $isNetworkMember,
                'order_type'          => $orderType,
                'source_channel'      => $sourceChannel,
                'status'              => 'PENDING',
                'total_amount'        => round($totalAmount, 2),
                'credit_amount'       => round($creditAmount, 2),
                'cash_amount'         => round($cashAmount, 2),
                'copay_status'        => 'NOT_REQUIRED',
                'notes'               => $notes,
                'submitted_at'        => $now,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            // STEP 5 — Create order lines
            foreach ($processedLines as $line) {
                DB::table('order_lines')->insert(array_merge($line, [
                    'order_id'   => $orderId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }

            Log::info('OrderPlacementService: order placed', [
                'order_id'    => $orderId,
                'ulid'        => $ulid,
                'facility_id' => $facilityId,
                'total'       => round($totalAmount, 2),
                'channel'     => $sourceChannel,
            ]);

            return [
                'order_id'    => $orderId,
                'ulid'        => $ulid,
                'status'      => 'PENDING',
                'total_amount' => round($totalAmount, 2),
                'credit_amount' => round($creditAmount, 2),
                'cash_amount'  => round($cashAmount, 2),
                'is_network_member' => $isNetworkMember,
                'source_channel'    => $sourceChannel,
            ];
        });
    }
}
