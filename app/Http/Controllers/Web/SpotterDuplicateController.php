<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\SpotterRegistrySnapshotJob;
use App\Models\SpotterDuplicateReview;
use Illuminate\Http\Request;

class SpotterDuplicateController extends Controller
{
    public function index()
    {
        $reviews = SpotterDuplicateReview::with(['submission.spotter:id,name', 'matchedSubmission'])
            ->where('decision', 'pending')
            ->oldest()
            ->paginate(15);

        return view('admin.spotter.duplicates.index', compact('reviews'));
    }

    public function decide(Request $request, SpotterDuplicateReview $review)
    {
        $request->validate([
            'decision' => 'required|in:confirmed_duplicate,not_duplicate',
            'notes' => 'nullable|string',
        ]);

        $review->update([
            'decision' => $request->decision,
            'reviewer_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->notes,
        ]);

        if ($request->decision === 'confirmed_duplicate') {
            $review->submission->update(['status' => 'rejected']);
        } elseif ($request->decision === 'not_duplicate') {
            if ($review->tier->value === 'sr') {
                $review->submission->update(['status' => 'sr_reviewed']);
                SpotterDuplicateReview::create([
                    'spotter_submission_id' => $review->spotter_submission_id,
                    'matched_submission_id' => $review->matched_submission_id,
                    'tier' => 'cc',
                    'decision' => 'pending',
                    'gps_distance_metres' => $review->gps_distance_metres,
                    'name_edit_distance' => $review->name_edit_distance,
                    'match_name' => $review->match_name,
                ]);
            } elseif ($review->tier->value === 'cc') {
                $review->submission->update(['status' => 'cc_verified']);
                SpotterDuplicateReview::create([
                    'spotter_submission_id' => $review->spotter_submission_id,
                    'matched_submission_id' => $review->matched_submission_id,
                    'tier' => 'admin',
                    'decision' => 'pending',
                    'gps_distance_metres' => $review->gps_distance_metres,
                    'name_edit_distance' => $review->name_edit_distance,
                    'match_name' => $review->match_name,
                ]);
            } elseif ($review->tier->value === 'admin') {
                $review->submission->update(['status' => 'accepted']);
                SpotterRegistrySnapshotJob::dispatch();
            }
        }

        return redirect()->route('admin.spotter.duplicates.index')->with('success', 'Decision recorded');
    }
}
