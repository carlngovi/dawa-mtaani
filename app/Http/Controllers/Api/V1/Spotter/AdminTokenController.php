<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Models\SpotterToken;
use App\Models\User;
use App\Services\SpotterActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTokenController extends Controller
{
    public function index(): JsonResponse
    {
        $tokens = SpotterToken::with('spotter:id,name')
            ->latest()
            ->paginate(25);

        return response()->json($tokens);
    }

    public function generateCode(Request $request): JsonResponse
    {
        $request->validate([
            'spotter_user_id' => 'required|exists:users,id',
            'expiry_hours' => 'integer|min:1|max:168',
        ]);

        $spotter = User::findOrFail($request->spotter_user_id);

        $code = app(SpotterActivationService::class)->generateCode(
            auth()->user(),
            $spotter,
            $request->expiry_hours ?? 72,
        );

        $display = implode('-', str_split($code->code, 4));

        return response()->json([
            'code' => $display,
            'expires_at' => $code->expires_at->toISOString(),
        ]);
    }

    public function revoke(SpotterToken $token): JsonResponse
    {
        $token->update(['revoked_at' => now()]);

        return response()->json(['revoked' => true]);
    }
}
