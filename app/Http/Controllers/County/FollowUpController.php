<?php

namespace App\Http\Controllers\County;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterFollowUp;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterFollowUp::with(['submission:id,pharmacy,ward,county', 'spotter:id,name'])
            ->whereHas('submission', fn ($q) => $q->where('county', $scope['county']));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $followups = $query->orderByDesc('follow_up_date')->paginate(25)->withQueryString();

        return view('county.followups.index', compact('followups'));
    }
}
