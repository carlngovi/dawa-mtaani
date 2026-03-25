<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthTokenController
 *
 * Issues and revokes Sanctum personal access tokens for API clients.
 * Used by the Android offline-sync app and any external API consumers.
 *
 * POST /api/v1/auth/token   — issue token (returns plaintext token once)
 * DELETE /api/v1/auth/token — revoke current token
 * GET  /api/v1/auth/me      — return authenticated user + roles
 */
class AuthTokenController extends Controller
{
    /**
     * Issue a Sanctum personal access token.
     * Returns the plaintext token once — it cannot be retrieved again.
     */
    public function issue(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        // Revoke all existing tokens for this device name before issuing new one
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name, ['*'], now()->addDays(30));

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(30)->toISOString(),
            'user'       => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames(),
                'facility_id' => $user->facility_id ?? null,
            ],
        ], 201);
    }

    /**
     * Revoke the current token (logout from API).
     */
    public function revoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked.']);
    }

    /**
     * Return the authenticated user, their roles, and permissions.
     * Useful for clients to bootstrap their session state after token issue.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'facility_id' => $user->facility_id ?? null,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
