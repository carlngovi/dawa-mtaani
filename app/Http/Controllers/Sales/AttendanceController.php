<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\HasSpotterScope;
use App\Models\SpotterAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use HasSpotterScope;

    public function index(Request $request)
    {
        $scope = $this->spotterScope();

        $query = SpotterAttendance::with('spotter:id,name')
            ->whereIn('spotter_user_id', $scope['spotterIds']);

        if ($request->filled('spotter_id')) {
            $query->where('spotter_user_id', $request->spotter_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $attendance = $query->orderByDesc('date')->orderByDesc('clock_in_at')->paginate(25)->withQueryString();
        $spotterUsers = User::whereIn('id', $scope['spotterIds'])->pluck('name', 'id');

        return view('sales.attendance.index', compact('attendance', 'spotterUsers'));
    }
}
