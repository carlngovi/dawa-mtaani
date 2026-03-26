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

class TechWriteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasRole('technical_admin')) {
            return redirect('/dashboard');
        }
        $pendingApprovals = 0;
        if (Schema::hasTable('t0_approval_requests')) {
            $pendingApprovals = DB::table('t0_approval_requests')
                ->where('status', 'PENDING')
                ->count();
        }
        return view('tech.write', compact('pendingApprovals'));
    }
}
