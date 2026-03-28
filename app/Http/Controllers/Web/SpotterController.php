<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterSubmission;
use App\Models\SpotterToken;

class SpotterController extends Controller
{
    use HasSpotterScope;

    public function index()
    {
        $scope = $this->spotterScope();

        $submissionQuery = SpotterSubmission::whereDate('submitted_at', today());
        $this->applySubmissionScope($submissionQuery, $scope);
        $submissionsToday = $submissionQuery->count();

        if ($scope['isSalesRep']) {
            $pendingDuplicates = SpotterDuplicateReview::where('tier', 'sr')
                ->where('decision', 'pending')
                ->whereHas('submission', fn ($q) => $q->whereIn('spotter_user_id', $scope['spotterIds']))
                ->count();
        } elseif ($scope['isCC']) {
            $pendingDuplicates = SpotterDuplicateReview::where('tier', 'cc')
                ->where('decision', 'pending')
                ->whereHas('submission', fn ($q) => $q->where('county', $scope['county']))
                ->count();
        } else {
            $pendingDuplicates = SpotterDuplicateReview::where('decision', 'pending')->count();
        }

        $followUpQuery = SpotterFollowUp::where('status', 'overdue');
        if ($scope['isSalesRep']) {
            $followUpQuery->whereIn('spotter_user_id', $scope['spotterIds']);
        } elseif ($scope['isCC']) {
            $followUpQuery->whereHas('submission', fn ($q) => $q->where('county', $scope['county']));
        }
        $overdueFollowUps = $followUpQuery->count();

        $activeTokens = $scope['isAdmin'] ? SpotterToken::valid()->count() : null;
        $roleLabel = $this->roleLabel($scope);
        $spotterCount = $scope['spotterIds']->count();
        $county = $scope['county'];

        return view('admin.spotter.index', array_merge(compact(
            'submissionsToday',
            'pendingDuplicates',
            'overdueFollowUps',
            'activeTokens',
            'roleLabel',
            'spotterCount',
            'county',
        ), $scope));
    }
}
