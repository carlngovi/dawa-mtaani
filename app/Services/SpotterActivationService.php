<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Services;

use App\Models\SpotterActivationCode;
use App\Models\SpotterProfile;
use App\Models\User;
use App\Models\WardAdjacency;
use Illuminate\Validation\ValidationException;

class SpotterActivationService
{
    public function __construct(
        private SpotterTokenService $tokenService,
    ) {}

    public function generateCode(User $admin, User $spotter, int $expiryHours = 72): SpotterActivationCode
    {
        $code = strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));

        return SpotterActivationCode::create([
            'spotter_user_id' => $spotter->id,
            'code' => $code,
            'expires_at' => now()->addHours($expiryHours),
            'created_by' => $admin->id,
        ]);
    }

    public function activate(string $rawCode, ?string $deviceFingerprint): array
    {
        $cleaned = strtoupper(str_replace('-', '', $rawCode));

        $activation = SpotterActivationCode::unused()
            ->where('code', $cleaned)
            ->first();

        if (! $activation) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired activation code',
            ]);
        }

        $spotter = User::findOrFail($activation->spotter_user_id);

        if (! $spotter->hasRole('network_field_agent')) {
            throw ValidationException::withMessages([
                'code' => 'User does not have the required field agent role',
            ]);
        }

        $activation->update(['consumed_at' => now()]);

        // Load county/ward/salesRep from SpotterProfile
        $profile = SpotterProfile::where('user_id', $spotter->id)->first();

        if ($profile) {
            $county = $profile->county;
            $ward = $profile->ward;
            $salesRepName = $profile->getSalesRepName();
        } else {
            // Fallback: try the spotter's linked facility
            $county = '';
            $ward = '';
            $salesRepName = null;

            if ($spotter->facility_id) {
                $facility = \App\Models\Facility::find($spotter->facility_id);
                if ($facility) {
                    $county = $facility->county ?? '';
                    $ward = $facility->ward ?? '';
                }
            }
        }

        $tokens = $this->tokenService->issue(
            $spotter,
            $county,
            $ward,
            $salesRepName,
            $deviceFingerprint,
        );

        $token = \App\Models\SpotterToken::where('token_hash', hash('sha256', $tokens['token']))->first();

        return [
            'token' => $tokens['token'],
            'refresh_token' => $tokens['refresh_token'],
            'profile' => $this->tokenService->buildProfile($spotter, $token),
            'ward_adjacency' => WardAdjacency::getAdjacentWardMap($county),
        ];
    }
}
