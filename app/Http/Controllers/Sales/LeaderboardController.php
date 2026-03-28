<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterSubmission;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();
        $period = $request->query('period', 'programme');

        $dateFilter = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };

        $query = SpotterSubmission::query()
            ->whereNotIn('status', ['draft'])
            ->whereIn('spotter_user_id', $scope['spotterIds'])
            ->selectRaw("spotter_user_id, COUNT(*) as submissions, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as activations")
            ->groupBy('spotter_user_id');

        if ($dateFilter) {
            $query->where('submitted_at', '>=', $dateFilter);
        }

        $rows = $query->orderByDesc('activations')->orderByDesc('submissions')->get();
        $users = User::whereIn('id', $rows->pluck('spotter_user_id'))->pluck('name', 'id');

        $leaderboard = $rows->map(fn ($r, $i) => [
            'name' => $users[$r->spotter_user_id] ?? 'Unknown',
            'submissions' => (int) $r->submissions,
            'activations' => (int) $r->activations,
            'rank' => $i + 1,
        ])->values();

        $scopeLabel = 'Your Spotters';

        return view('sales.leaderboard.index', compact('leaderboard', 'period', 'scopeLabel'));
    }
}
