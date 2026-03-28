<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterSubmission;

class SpotterMapController extends Controller
{
    use HasSpotterScope;

    public function index()
    {
        $scope = $this->spotterScope();

        $query = SpotterSubmission::whereNotIn('status', ['draft', 'rejected'])
            ->select(['id', 'pharmacy', 'ward', 'county', 'potential', 'status', 'lat', 'lng', 'spotter_user_id', 'submitted_at']);

        $this->applySubmissionScope($query, $scope);

        $submissions = $query->get();

        return view('admin.spotter.map.index', compact('submissions', 'scope'));
    }
}
