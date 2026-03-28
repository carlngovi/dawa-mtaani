<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Services;

use App\Models\SpotterToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class SpotterTokenService
{
    public function issue(
        User $spotter,
        string $county,
        string $ward,
        ?string $salesRepName = null,
        ?string $deviceFingerprint = null,
    ): array {
        $rawToken = bin2hex(random_bytes(64));
        $rawRefresh = bin2hex(random_bytes(64));
        $tokenHash = hash('sha256', $rawToken);
        $refreshHash = hash('sha256', $rawRefresh);

        SpotterToken::create([
            'spotter_user_id' => $spotter->id,
            'token_hash' => $tokenHash,
            'refresh_token_hash' => $refreshHash,
            'device_fingerprint' => $deviceFingerprint,
            'county' => $county,
            'ward' => $ward,
            'sales_rep_name' => $salesRepName,
            'expires_at' => now()->addHours(24),
            'refresh_expires_at' => now()->addDays(30),
        ]);

        return ['token' => $rawToken, 'refresh_token' => $rawRefresh];
    }

    public function refresh(string $rawRefreshToken): array
    {
        $hash = hash('sha256', $rawRefreshToken);
        $old = SpotterToken::where('refresh_token_hash', $hash)->first();

        if (! $old || ! $old->isRefreshValid()) {
            throw new AuthenticationException('Refresh token invalid or expired');
        }

        $old->update(['revoked_at' => now()]);

        return $this->issue(
            $old->spotter,
            $old->county,
            $old->ward,
            $old->sales_rep_name,
            $old->device_fingerprint,
        );
    }

    public function resolve(string $rawToken): ?SpotterToken
    {
        return SpotterToken::findByToken($rawToken);
    }

    public function buildProfile(User $spotter, SpotterToken $token): array
    {
        return [
            'id' => $spotter->id,
            'name' => $spotter->name,
            'county' => $token->county,
            'ward' => $token->ward,
            'salesRep' => $token->sales_rep_name,
        ];
    }
}
