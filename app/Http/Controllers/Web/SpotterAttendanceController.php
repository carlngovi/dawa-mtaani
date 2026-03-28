<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterAttendance;
use App\Models\SpotterProfile;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterAttendanceController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterAttendance::with('spotter:id,name');

        if ($scope['isSalesRep']) {
            $query->whereIn('spotter_user_id', $scope['spotterIds']);
        } elseif ($scope['isCC']) {
            $countySpotterIds = SpotterProfile::where('county', $scope['county'])->pluck('user_id');
            $query->whereIn('spotter_user_id', $countySpotterIds);
        }

        if ($request->filled('spotter_id')) {
            $query->where('spotter_user_id', $request->spotter_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $attendances = $query->orderByDesc('date')->orderByDesc('clock_in_at')->paginate(25)->withQueryString();

        if ($scope['isSalesRep']) {
            $spotters = User::whereIn('id', $scope['spotterIds'])->get(['id', 'name']);
        } elseif ($scope['isCC']) {
            $spotters = User::whereIn('id', $countySpotterIds ?? collect())->get(['id', 'name']);
        } else {
            $spotters = User::role('network_field_agent')->get(['id', 'name']);
        }

        return view('admin.spotter.attendance.index', compact('attendances', 'spotters'));
    }
}
