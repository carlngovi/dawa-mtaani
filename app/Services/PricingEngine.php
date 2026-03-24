<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PricingEngine
{
    public function applyAgreement(int $facilityId, float $baseUnitPrice): float
    {
        try {
            $agreement = DB::table('facility_pricing_agreements')
                ->where('facility_id', $facilityId)
                ->where('effective_from', '<=', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now()->toDateString());
                })
                ->first();

            if (! $agreement) {
                return $baseUnitPrice;
            }

            return match ($agreement->premium_type) {
                'PERCENTAGE'   => $baseUnitPrice * (1 + $agreement->premium_value / 100),
                'FIXED_MARGIN' => $baseUnitPrice + $agreement->premium_value,
                default        => $baseUnitPrice,
            };

        } catch (\Throwable $e) {
            Log::error('PricingEngine: failed to apply agreement', [
                'facility_id'     => $facilityId,
                'base_unit_price' => $baseUnitPrice,
                'error'           => $e->getMessage(),
            ]);

            // On failure return base price — never block an order
            return $baseUnitPrice;
        }
    }

    public function calculatePremiumAmount(int $facilityId, float $baseUnitPrice): float
    {
        $finalPrice = $this->applyAgreement($facilityId, $baseUnitPrice);
        return round($finalPrice - $baseUnitPrice, 2);
    }

    public function isOffNetwork(int $facilityId): bool
    {
        return DB::table('facilities')
            ->where('id', $facilityId)
            ->value('network_membership') === 'OFF_NETWORK';
    }
}
