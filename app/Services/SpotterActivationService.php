<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\SpotterActivationCode;
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

        // Derive county/ward from the spotter's linked facility
        $county = '';
        $ward = '';
        if ($spotter->facility_id) {
            $facility = Facility::find($spotter->facility_id);
            if ($facility) {
                $county = $facility->county ?? '';
                $ward = $facility->ward ?? '';
            }
        }
        // TODO: If spotter has no facility, county/ward should come from a spotter profile table

        // TODO: Look up sales rep name once a spotter↔sales_rep relationship exists
        $salesRepName = null;

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
            'ward_adjacency' => WardAdjacency::getAdjacentWardMap(''),
        ];
    }
}
