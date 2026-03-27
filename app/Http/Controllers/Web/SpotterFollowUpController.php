<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterFollowUp;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterFollowUpController extends Controller
{
    public function index(Request $request)
    {
        $query = SpotterFollowUp::with(['submission:id,pharmacy,ward', 'spotter:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('spotter_id')) {
            $query->where('spotter_user_id', $request->spotter_id);
        }

        $followUps = $query->orderByDesc('follow_up_date')->paginate(25)->withQueryString();
        $spotters = User::role('network_field_agent')->get(['id', 'name']);

        return view('admin.spotter.followups.index', compact('followUps', 'spotters'));
    }
}
