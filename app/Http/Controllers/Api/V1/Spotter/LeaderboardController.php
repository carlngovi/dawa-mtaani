<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Models\SpotterSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $spotter = $request->spotter_token->spotter;

        $supervisorRoles = ['sales_rep', 'county_coordinator', 'admin', 'super_admin', 'technical_admin'];
        if (! $spotter->hasAnyRole($supervisorRoles)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $period = $request->query('period', 'programme');

        $dateFilter = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };

        $query = SpotterSubmission::query()
            ->whereNotIn('status', ['draft'])
            ->selectRaw('spotter_user_id,
                COUNT(*) as submissions,
                SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as activations')
            ->groupBy('spotter_user_id');

        if ($dateFilter) {
            $query->where('submitted_at', '>=', $dateFilter);
        }

        $rows = $query->get();
        $userIds = $rows->pluck('spotter_user_id');
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        $leaderboard = $rows->map(fn ($r) => [
            'name' => $users[$r->spotter_user_id] ?? 'Unknown',
            'submissions' => (int) $r->submissions,
            'activations' => (int) $r->activations,
            'me' => $r->spotter_user_id === $spotter->id,
        ])->sortByDesc('submissions')->values();

        return response()->json(compact('leaderboard', 'period'));
    }
}
