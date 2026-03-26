<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuperT0ApprovalsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasRole('super_admin')) {
            return redirect('/dashboard');
        }
        $approvals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        if (Schema::hasTable('t0_approval_requests')) {
            $approvals = DB::table('t0_approval_requests')
                ->orderByRaw("CASE WHEN status='PENDING' THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        return view('super.t0-approvals', compact('approvals'));
    }

    public function confirm($id)
    {
        if (! Auth::user()->hasRole('super_admin')) abort(403);
        if (Schema::hasTable('t0_approval_requests')) {
            DB::table('t0_approval_requests')
                ->where('id', $id)
                ->update([
                    'status'      => 'APPROVED',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                ]);
        }
        return back()->with('success', 'Write operation approved.');
    }

    public function reject($id)
    {
        if (! Auth::user()->hasRole('super_admin')) abort(403);
        if (Schema::hasTable('t0_approval_requests')) {
            DB::table('t0_approval_requests')
                ->where('id', $id)
                ->update([
                    'status'      => 'REJECTED',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                ]);
        }
        return back()->with('success', 'Write operation rejected.');
    }
}
