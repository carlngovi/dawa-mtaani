<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterSubmission;
use App\Models\SpotterToken;

class SpotterController extends Controller
{
    public function index()
    {
        $submissionsToday = SpotterSubmission::whereDate('submitted_at', today())->count();
        $pendingDuplicates = SpotterDuplicateReview::where('decision', 'pending')->count();
        $overdueFollowUps = SpotterFollowUp::where('status', 'overdue')->count();
        $activeTokens = SpotterToken::valid()->count();

        return view('admin.spotter.index', compact(
            'submissionsToday',
            'pendingDuplicates',
            'overdueFollowUps',
            'activeTokens',
        ));
    }
}
