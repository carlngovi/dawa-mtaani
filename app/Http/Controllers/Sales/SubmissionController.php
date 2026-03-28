<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterSubmission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterSubmission::with('spotter:id,name')
            ->whereIn('spotter_user_id', $scope['spotterIds']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('submitted_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('submitted_at', '<=', $request->to);
        }

        $submissions = $query->orderByDesc('submitted_at')->paginate(25)->withQueryString();

        return view('sales.submissions.index', compact('submissions'));
    }

    public function show(SpotterSubmission $submission)
    {
        $scope = $this->spotterScope();
        abort_if(! $scope['spotterIds']->contains($submission->spotter_user_id), 403);

        $submission->load('followUp', 'duplicateReviews.reviewer', 'spotter:id,name');

        return view('sales.submissions.show', compact('submission'));
    }
}
