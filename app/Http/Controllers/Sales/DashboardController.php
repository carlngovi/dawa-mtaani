<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterSubmission;
use App\Models\User;

class DashboardController extends Controller
{
    use HasSpotterScope;

    public function index()
    {
        $scope = $this->spotterScope();

        $submissionsToday = SpotterSubmission::whereIn('spotter_user_id', $scope['spotterIds'])
            ->whereDate('submitted_at', today())->count();

        $pendingDuplicates = SpotterDuplicateReview::where('tier', 'sr')
            ->where('decision', 'pending')
            ->whereHas('submission', fn ($q) => $q->whereIn('spotter_user_id', $scope['spotterIds']))
            ->count();

        $overdueFollowUps = SpotterFollowUp::where('status', 'overdue')
            ->whereIn('spotter_user_id', $scope['spotterIds'])->count();

        $spotterCount = $scope['spotterIds']->count();

        $mySpotters = User::whereIn('id', $scope['spotterIds'])
            ->get(['id', 'name'])
            ->map(function ($spotter) {
                $spotter->total_submissions = SpotterSubmission::where('spotter_user_id', $spotter->id)
                    ->whereNotIn('status', ['draft'])->count();
                $spotter->today_submissions = SpotterSubmission::where('spotter_user_id', $spotter->id)
                    ->whereDate('submitted_at', today())->count();

                return $spotter;
            });

        return view('sales.dashboard', compact(
            'submissionsToday',
            'pendingDuplicates',
            'overdueFollowUps',
            'spotterCount',
            'mySpotters',
        ));
    }
}
