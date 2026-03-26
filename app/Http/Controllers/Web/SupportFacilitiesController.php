<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SupportFacilitiesController
 *
 * admin_support, read-only facility lookup
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportFacilitiesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin_support', 'admin', 'super_admin', 'assistant_admin'])) {
            return redirect('/dashboard');
        }

        $facilities = collect();
        $searched   = false;

        if ($request->filled('search') && strlen($request->search) >= 3) {
            $searched = true;
            $q        = $request->search;

            $facilities = DB::table('facilities')
                ->where(function ($query) use ($q) {
                    $query->where('facility_name', 'like', "%{$q}%")
                          ->orWhere('ppb_licence',  'like', "%{$q}%")
                          ->orWhere('phone',        'like', "%{$q}%");
                })
                ->whereNull('deleted_at')
                ->select([
                    'ulid', 'facility_name', 'ppb_licence', 'facility_type',
                    'county', 'facility_status', 'network_membership', 'phone', 'created_at',
                ])
                ->limit(20)
                ->get();
        }

        return view('support.facilities', compact('facilities', 'searched'));
    }
}
