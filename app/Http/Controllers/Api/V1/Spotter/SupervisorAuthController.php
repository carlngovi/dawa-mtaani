<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Models\SpotterProfile;
use App\Models\SpotterToken;
use App\Models\User;
use App\Services\SpotterTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupervisorAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $supervisorRoles = ['sales_rep', 'county_coordinator', 'admin', 'super_admin', 'technical_admin'];
        if (! $user->hasAnyRole($supervisorRoles)) {
            return response()->json(['error' => 'Access denied. This portal is for supervisors only.'], 403);
        }

        $role = $user->roles->first()?->name;

        $profile = SpotterProfile::where('user_id', $user->id)->first();
        $county = $profile?->county ?? '';
        $ward = $profile?->ward ?? '';

        $tokenService = app(SpotterTokenService::class);
        $tokens = $tokenService->issue($user, $county, $ward, null, null);

        return response()->json([
            'token' => $tokens['token'],
            'refresh_token' => $tokens['refresh_token'],
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $role,
                'county' => $county,
                'ward' => $ward,
                'salesRep' => null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $rawToken = substr($request->header('Authorization', ''), 7);
        if ($rawToken) {
            $token = SpotterToken::findByToken($rawToken);
            $token?->update(['revoked_at' => now()]);
        }

        return response()->json(['status' => 'logged_out']);
    }
}
