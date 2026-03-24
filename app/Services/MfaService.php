<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MfaService
{
    public function request(
        int $userId,
        string $operationType,
        string $method = 'OTP_SMS'
    ): array {
        // Check if operation is locked for this user
        if ($this->isLocked($userId, $operationType)) {
            return [
                'success' => false,
                'locked'  => true,
                'message' => 'Too many failed attempts. This operation is locked for 1 hour.',
            ];
        }

        $code  = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);
        $now   = Carbon::now('UTC');
        $expiryMinutes = config('security.mfa.otp_expiry_minutes', 10);

        DB::table('mfa_requests')->insert([
            'user_id'             => $userId,
            'operation_type'      => $operationType,
            'verification_method' => $method,
            'verification_code'   => $code,
            'verification_token'  => $token,
            'expires_at'          => $now->copy()->addMinutes($expiryMinutes),
            'created_at'          => $now,
        ]);

        // TODO: send OTP via SMS or WhatsApp when integrations are live
        Log::info('MfaService: OTP generated', [
            'user_id'        => $userId,
            'operation_type' => $operationType,
            'method'         => $method,
        ]);

        return [
            'success' => true,
            'token'   => $token,
            'message' => 'OTP sent.',
        ];
    }

    public function verify(
        int $userId,
        string $operationType,
        string $code,
        string $ip
    ): bool {
        // Check lock
        if ($this->isLocked($userId, $operationType)) {
            return false;
        }

        $record = DB::table('mfa_requests')
            ->where('user_id', $userId)
            ->where('operation_type', $operationType)
            ->where('verification_code', $code)
            ->where('expires_at', '>', Carbon::now('UTC'))
            ->whereNull('verified_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $record) {
            // Log failed attempt
            $this->logSecurityEvent($userId, 'LOGIN_FAILURE', 'MEDIUM', [
                'operation_type' => $operationType,
                'reason'         => 'Invalid or expired OTP',
            ], $ip);

            return false;
        }

        DB::table('mfa_requests')
            ->where('id', $record->id)
            ->update([
                'verified_at'    => Carbon::now('UTC'),
                'verified_by_ip' => $ip,
            ]);

        return true;
    }

    public function isLocked(int $userId, string $operationType): bool
    {
        $maxAttempts = config('security.mfa.max_attempts_before_lock', 3);
        $lockMinutes = config('security.mfa.lock_duration_minutes', 60);
        $since = Carbon::now('UTC')->subMinutes($lockMinutes);

        $recentFailures = DB::table('mfa_requests')
            ->where('user_id', $userId)
            ->where('operation_type', $operationType)
            ->whereNull('verified_at')
            ->where('created_at', '>=', $since)
            ->count();

        return $recentFailures >= $maxAttempts;
    }

    public function requiresMfa(string $operationType, mixed $context = null): bool
    {
        return match ($operationType) {
            'CREDIT_DRAW' => $this->creditDrawExceedsThreshold($context),
            'PRICE_LIST_CHANGE' => $this->priceListChangeIsBulk($context),
            'FACILITY_STATUS_CHANGE', 'ROLE_CHANGE', 'PAYMENT_APPROVAL' => true,
            default => false,
        };
    }

    private function creditDrawExceedsThreshold(mixed $amount): bool
    {
        if (! is_numeric($amount)) return false;

        $threshold = (float) (DB::table('system_settings')
            ->where('key', 'mfa_credit_draw_threshold_kes')
            ->value('value') ?? 50000);

        return (float) $amount >= $threshold;
    }

    private function priceListChangeIsBulk(mixed $count): bool
    {
        return is_numeric($count) && (int) $count >= 10;
    }

    private function logSecurityEvent(
        int $userId,
        string $eventType,
        string $severity,
        array $details,
        string $ip
    ): void {
        try {
            DB::table('security_events')->insert([
                'user_id'    => $userId,
                'event_type' => $eventType,
                'severity'   => $severity,
                'details'    => json_encode($details),
                'ip_address' => $ip,
                'created_at' => Carbon::now('UTC'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MfaService: failed to log security event', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
