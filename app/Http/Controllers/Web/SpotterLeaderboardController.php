<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterSubmission;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterLeaderboardController extends Controller
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
            ->selectRaw('spotter_user_id,
                COUNT(*) as submissions,
                SUM(CASE WHEN status = \'accepted\' THEN 1 ELSE 0 END) as activations')
            ->groupBy('spotter_user_id');

        $this->applySubmissionScope($query, $scope);

        if ($dateFilter) {
            $query->where('submitted_at', '>=', $dateFilter);
        }

        $rows = $query->orderByDesc('activations')->orderByDesc('submissions')->get();
        $userIds = $rows->pluck('spotter_user_id');
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        $leaderboard = $rows->map(fn ($r, $i) => [
            'name' => $users[$r->spotter_user_id] ?? 'Unknown',
            'submissions' => (int) $r->submissions,
            'activations' => (int) $r->activations,
            'rank' => $i + 1,
        ])->values();

        $scopeLabel = $scope['isSalesRep'] ? 'Your Spotters'
            : ($scope['isCC'] ? ($scope['county'].' County') : 'All Counties');

        return view('admin.spotter.leaderboard.index', compact('leaderboard', 'period', 'scopeLabel', 'scope'));
    }
}
