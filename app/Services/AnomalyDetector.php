<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnomalyDetector
{
    public function checkLogin(int $userId, string $ip, string $userAgent): void
    {
        $this->checkUnusualHour($userId, $ip, $userAgent);
        $this->checkUnusualLocation($userId, $ip, $userAgent);
    }

    public function checkOrderSubmission(int $facilityId, string $ip): void
    {
        $fiveMinutesAgo = Carbon::now('UTC')->subMinutes(5);

        $recentOrders = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->whereNull('deleted_at')
            ->count();

        if ($recentOrders >= 10) {
            $this->logEvent(
                userId: null,
                facilityId: $facilityId,
                eventType: 'RAPID_ORDER_SUBMISSION',
                severity: 'HIGH',
                details: [
                    'orders_in_5_minutes' => $recentOrders,
                    'threshold'           => 10,
                ],
                ip: $ip
            );

            Log::warning('AnomalyDetector: rapid order submission detected', [
                'facility_id'         => $facilityId,
                'orders_in_5_minutes' => $recentOrders,
            ]);
        }
    }

    public function checkPriceChange(
        int $wholesaleFacilityId,
        int $productId,
        float $oldPrice,
        float $newPrice,
        string $ip
    ): void {
        if ($oldPrice <= 0) return;

        $changePct = (($newPrice - $oldPrice) / $oldPrice) * 100;

        if ($changePct > 50) {
            $this->logEvent(
                userId: null,
                facilityId: $wholesaleFacilityId,
                eventType: 'SUSPICIOUS_PRICE_CHANGE',
                severity: 'HIGH',
                details: [
                    'product_id'   => $productId,
                    'old_price'    => $oldPrice,
                    'new_price'    => $newPrice,
                    'change_pct'   => round($changePct, 2),
                ],
                ip: $ip
            );

            Log::warning('AnomalyDetector: suspicious price change detected', [
                'product_id' => $productId,
                'change_pct' => round($changePct, 2),
            ]);
        }
    }

    private function checkUnusualHour(int $userId, string $ip, string $userAgent): void
    {
        $currentHour = Carbon::now('Africa/Nairobi')->hour;

        // Get typical login hours for this user (last 30 days)
        $typicalHours = DB::table('security_events')
            ->where('user_id', $userId)
            ->whereIn('event_type', ['LOGIN_SUCCESS_UNUSUAL_HOUR'])
            ->where('created_at', '>=', Carbon::now('UTC')->subDays(30))
            ->pluck('details')
            ->map(fn ($d) => json_decode($d, true)['hour'] ?? null)
            ->filter()
            ->toArray();

        // Need at least 5 logins to establish a pattern
        if (count($typicalHours) < 5) return;

        $avgHour = array_sum($typicalHours) / count($typicalHours);
        $deviation = abs($currentHour - $avgHour);

        // Flag if login is more than 4 hours outside typical pattern
        if ($deviation > 4) {
            $this->logEvent(
                userId: $userId,
                facilityId: null,
                eventType: 'LOGIN_SUCCESS_UNUSUAL_HOUR',
                severity: 'MEDIUM',
                details: [
                    'hour'      => $currentHour,
                    'avg_hour'  => round($avgHour, 1),
                    'deviation' => round($deviation, 1),
                ],
                ip: $ip,
                userAgent: $userAgent
            );
        }
    }

    private function checkUnusualLocation(int $userId, string $ip, string $userAgent): void
    {
        // Get last known IP for this user
        $lastEvent = DB::table('security_events')
            ->where('user_id', $userId)
            ->whereNotNull('ip_address')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastEvent || $lastEvent->ip_address === $ip) return;

        // Simple check — different IP subnet
        $lastSubnet    = implode('.', array_slice(explode('.', $lastEvent->ip_address), 0, 2));
        $currentSubnet = implode('.', array_slice(explode('.', $ip), 0, 2));

        if ($lastSubnet !== $currentSubnet) {
            $this->logEvent(
                userId: $userId,
                facilityId: null,
                eventType: 'LOGIN_SUCCESS_UNUSUAL_LOCATION',
                severity: 'MEDIUM',
                details: [
                    'current_ip'  => $ip,
                    'previous_ip' => $lastEvent->ip_address,
                ],
                ip: $ip,
                userAgent: $userAgent
            );
        }
    }

    private function logEvent(
        ?int $userId,
        ?int $facilityId,
        string $eventType,
        string $severity,
        array $details,
        string $ip,
        ?string $userAgent = null
    ): void {
        try {
            DB::table('security_events')->insert([
                'user_id'     => $userId,
                'facility_id' => $facilityId,
                'event_type'  => $eventType,
                'severity'    => $severity,
                'details'     => json_encode($details),
                'ip_address'  => $ip,
                'user_agent'  => $userAgent,
                'created_at'  => Carbon::now('UTC'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AnomalyDetector: failed to log event', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
