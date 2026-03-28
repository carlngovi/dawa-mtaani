<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterDuplicateReview;
use Illuminate\Http\Request;

class DuplicateController extends Controller
{
    use HasSpotterScope;

    public function index()
    {
        $scope = $this->spotterScope();

        $reviews = SpotterDuplicateReview::with(['submission.spotter:id,name', 'matchedSubmission'])
            ->where('tier', 'sr')
            ->where('decision', 'pending')
            ->whereHas('submission', fn ($q) => $q->whereIn('spotter_user_id', $scope['spotterIds']))
            ->oldest()->paginate(15);

        return view('sales.duplicates.index', compact('reviews'));
    }

    public function decide(Request $request, SpotterDuplicateReview $review)
    {
        $scope = $this->spotterScope();
        abort_if($review->tier->value !== 'sr', 403, 'Not a Sales Rep tier review');
        abort_if(! $scope['spotterIds']->contains($review->submission->spotter_user_id), 403);

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
        } else {
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
        }

        return redirect()->route('sales.duplicates.index')->with('success', 'Decision recorded');
    }
}
