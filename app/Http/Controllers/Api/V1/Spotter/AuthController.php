<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Services\SpotterTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        try {
            $tokens = app(SpotterTokenService::class)->refresh($request->refresh_token);

            return response()->json($tokens, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Refresh token invalid or expired'], 401);
        }
    }
}
