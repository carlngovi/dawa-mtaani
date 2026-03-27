<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = SpotterAttendance::with('spotter:id,name');

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
        $spotters = User::role('network_field_agent')->get(['id', 'name']);

        return view('admin.spotter.attendance.index', compact('attendances', 'spotters'));
    }
}
