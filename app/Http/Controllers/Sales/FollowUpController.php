<?php

namespace App\Http\Controllers\Sales;

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
            ->whereIn('spotter_user_id', $scope['spotterIds']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $followups = $query->orderByDesc('follow_up_date')->paginate(25)->withQueryString();

        return view('sales.followups.index', compact('followups'));
    }
}
