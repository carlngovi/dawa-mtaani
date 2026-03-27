<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Models\SpotterAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClockController extends Controller
{
    public function clockIn(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $user = $request->spotter_token->spotter;
        $today = now()->toDateString();

        $existing = SpotterAttendance::where('spotter_user_id', $user->id)
            ->where('date', $today)
            ->whereNull('clock_out_at')
            ->orderByDesc('split_shift_index')
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Already clocked in',
                'time' => $existing->clock_in_at->toISOString(),
            ], 409);
        }

        $shiftIndex = SpotterAttendance::where('spotter_user_id', $user->id)
            ->where('date', $today)
            ->count() + 1;

        $attendance = SpotterAttendance::create([
            'spotter_user_id' => $user->id,
            'server_id' => (string) Str::ulid(),
            'date' => $today,
            'clock_in_at' => now(),
            'clock_in_lat' => $request->lat,
            'clock_in_lng' => $request->lng,
            'split_shift_index' => $shiftIndex,
        ]);

        return response()->json([
            'status' => 'clocked_in',
            'time' => $attendance->clock_in_at->toISOString(),
            'server_id' => $attendance->server_id,
        ]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $user = $request->spotter_token->spotter;

        $open = SpotterAttendance::where('spotter_user_id', $user->id)
            ->where('date', now()->toDateString())
            ->whereNull('clock_out_at')
            ->latest()
            ->first();

        if (! $open) {
            return response()->json(['error' => 'No active clock-in found'], 409);
        }

        $open->update([
            'clock_out_at' => now(),
            'clock_out_lat' => $request->lat,
            'clock_out_lng' => $request->lng,
        ]);

        return response()->json([
            'status' => 'clocked_out',
            'time' => $open->fresh()->clock_out_at->toISOString(),
        ]);
    }
}
