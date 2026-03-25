<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MfaBackupCodeService
{
    private string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generate(int $userId): array
    {
        $codes = [];
        $now = Carbon::now('UTC');

        // Invalidate existing unused codes
        DB::table('mfa_backup_codes')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->update(['used_at' => $now]);

        // Generate 10 new codes
        for ($i = 0; $i < 10; $i++) {
            $code = $this->generateCode();
            $codes[] = $code;

            DB::table('mfa_backup_codes')->insert([
                'user_id'    => $userId,
                'code_hash'  => Hash::make($code),
                'created_at' => $now,
            ]);
        }

        // Log security event — codes generated
        DB::table('security_events')->insert([
            'user_id'    => $userId,
            'event_type' => 'LOGIN_SUCCESS_UNUSUAL_HOUR',
            'severity'   => 'INFO',
            'details'    => json_encode(['action' => 'backup_codes_generated']),
            'ip_address' => request()->ip() ?? '0.0.0.0',
            'created_at' => $now,
        ]);

        Log::info('MfaBackupCodeService: backup codes generated', [
            'user_id' => $userId,
            'count'   => count($codes),
        ]);

        // Return raw codes ONCE — never stored
        return $codes;
    }

    public function verify(int $userId, string $code): bool
    {
        $unusedCodes = DB::table('mfa_backup_codes')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->get();

        foreach ($unusedCodes as $storedCode) {
            if (Hash::check($code, $storedCode->code_hash)) {
                // Mark as used
                DB::table('mfa_backup_codes')
                    ->where('id', $storedCode->id)
                    ->update(['used_at' => Carbon::now('UTC')]);

                // Log security event
                DB::table('security_events')->insert([
                    'user_id'    => $userId,
                    'event_type' => 'MFA_BACKUP_CODE_USED',
                    'severity'   => 'INFO',
                    'details'    => json_encode(['backup_code_id' => $storedCode->id]),
                    'ip_address' => request()->ip() ?? '0.0.0.0',
                    'created_at' => Carbon::now('UTC'),
                ]);

                // Check if fewer than 3 codes remain
                $remaining = DB::table('mfa_backup_codes')
                    ->where('user_id', $userId)
                    ->whereNull('used_at')
                    ->count();

                if ($remaining < config('security.mfa.backup_codes_warn_threshold', 3)) {
                    Log::warning('MfaBackupCodeService: fewer than 3 backup codes remain', [
                        'user_id'   => $userId,
                        'remaining' => $remaining,
                    ]);
                }

                return true;
            }
        }

        return false;
    }

    public function getRemainingCount(int $userId): int
    {
        return DB::table('mfa_backup_codes')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->count();
    }

    public function regenerate(int $userId): array
    {
        return $this->generate($userId);
    }

    private function generateCode(): string
    {
        $code = '';
        $max = strlen($this->alphabet) - 1;

        for ($i = 0; $i < 8; $i++) {
            $code .= $this->alphabet[random_int(0, $max)];
        }

        return $code;
    }
}
