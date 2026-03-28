<?php

namespace App\Http\Controllers\County;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterSubmission;

class DashboardController extends Controller
{
    use HasSpotterScope;

    public function index()
    {
        $scope = $this->spotterScope();
        $county = $scope['county'];

        $submissionsToday = SpotterSubmission::where('county', $county)
            ->whereDate('submitted_at', today())->count();

        $pendingDuplicates = SpotterDuplicateReview::where('tier', 'cc')
            ->where('decision', 'pending')
            ->whereHas('submission', fn ($q) => $q->where('county', $county))
            ->count();

        $overdueFollowUps = SpotterFollowUp::where('status', 'overdue')
            ->whereHas('submission', fn ($q) => $q->where('county', $county))->count();

        $totalSubmissions = SpotterSubmission::where('county', $county)
            ->whereNotIn('status', ['draft'])->count();

        $acceptedCount = SpotterSubmission::where('county', $county)
            ->where('status', 'accepted')->count();

        return view('county.dashboard', compact(
            'submissionsToday',
            'pendingDuplicates',
            'overdueFollowUps',
            'totalSubmissions',
            'acceptedCount',
            'county',
        ));
    }
}
