<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Services\SpotterActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActivationController extends Controller
{
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'device_fingerprint' => 'nullable|string',
        ]);

        try {
            $result = app(SpotterActivationService::class)->activate(
                $request->code,
                $request->device_fingerprint,
            );

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
