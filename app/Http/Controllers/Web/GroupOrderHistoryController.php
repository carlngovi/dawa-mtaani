<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GroupOrderHistoryController
 *
 * group_owner, order history across member outlets
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupOrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $group = DB::table('pharmacy_groups')
            ->where('group_owner_user_id', $user->id)
            ->first();

        if (! $group) return redirect('/group/dashboard');

        $outletIds = DB::table('pharmacy_group_members')
            ->where('group_id', $group->id)
            ->pluck('facility_id');

        $orders = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->join('users as u',      'o.placer_user_id',     '=', 'u.id')
            ->whereIn('o.retail_facility_id', $outletIds)
            ->whereNull('o.deleted_at')
            ->when($request->filled('outlet'), fn($q) => $q->where('f.ulid', $request->outlet))
            ->when($request->filled('status'), fn($q) => $q->where('o.status', $request->status))
            ->select(['o.ulid', 'o.status', 'o.total_amount', 'o.payment_type',
                      'o.created_at', 'f.facility_name', 'u.name as placer_name'])
            ->orderBy('o.created_at', 'desc')
            ->paginate(25)->withQueryString();

        $outlets = DB::table('pharmacy_group_members as m')
            ->join('facilities as f', 'm.facility_id', '=', 'f.id')
            ->where('m.group_id', $group->id)
            ->select(['f.ulid', 'f.facility_name'])
            ->get();

        return view('group.orders', compact('orders', 'outlets', 'currency', 'group'));
    }
}
