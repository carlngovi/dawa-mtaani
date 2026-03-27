<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterSubmission;
use Illuminate\Http\Request;

class SpotterSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = SpotterSubmission::with('spotter:id,name')
            ->select(array_merge(
                ['id'],
                array_diff(
                    (new SpotterSubmission)->getFillable(),
                    SpotterSubmission::CONFIDENTIAL,
                ),
            ));

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
        $submission->load('followUp', 'duplicateReviews.reviewer', 'spotter:id,name');

        return view('admin.spotter.submissions.show', compact('submission'));
    }
}
