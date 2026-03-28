<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterSubmission;
use Illuminate\Http\Request;

class SpotterSubmissionController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterSubmission::with('spotter:id,name')
            ->select(array_merge(
                ['id'],
                array_diff(
                    (new SpotterSubmission)->getFillable(),
                    SpotterSubmission::CONFIDENTIAL,
                ),
            ));

        $this->applySubmissionScope($query, $scope);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('county')) {
            $query->where('county', 'like', '%' . $request->county . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('visit_date', '<=', $request->date_to);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.spotter.submissions.index', compact('submissions'));
    }

    public function show(SpotterSubmission $submission)
    {
        $scope = $this->spotterScope();

        if ($scope['isSalesRep'] && ! $scope['spotterIds']->contains($submission->spotter_user_id)) {
            abort(403, 'You do not have access to this submission.');
        }
        if ($scope['isCC'] && $submission->county !== $scope['county']) {
            abort(403, 'You do not have access to this submission.');
        }

        $submission->load('followUp', 'duplicateReviews.reviewer', 'spotter:id,name');

        return view('admin.spotter.submissions.show', compact('submission'));
    }
}
