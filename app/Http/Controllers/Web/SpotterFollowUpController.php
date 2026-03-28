<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterFollowUp;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterFollowUpController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterFollowUp::with(['submission:id,pharmacy,ward,county', 'spotter:id,name']);

        if ($scope['isSalesRep']) {
            $query->whereIn('spotter_user_id', $scope['spotterIds']);
        } elseif ($scope['isCC']) {
            $query->whereHas('submission', fn ($q) => $q->where('county', $scope['county']));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('spotter_id')) {
            $query->where('spotter_user_id', $request->spotter_id);
        }

        $followUps = $query->orderByDesc('follow_up_date')->paginate(25)->withQueryString();

        if ($scope['isSalesRep']) {
            $spotters = User::whereIn('id', $scope['spotterIds'])->get(['id', 'name']);
        } else {
            $spotters = User::role('network_field_agent')->get(['id', 'name']);
        }

        return view('admin.spotter.followups.index', compact('followUps', 'spotters'));
    }
}
