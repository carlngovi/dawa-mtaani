<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GroupDashboardController
 *
 * group_owner, consolidated group overview
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupDashboardController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $group = DB::table('pharmacy_groups')
            ->where('group_owner_user_id', $user->id)
            ->first();

        if (! $group) {
            return view('group.dashboard', [
                'group'              => null,
                'outlets'            => collect(),
                'outletCredit'       => collect(),
                'outletOrders'       => collect(),
                'authorisedOutletIds'=> collect(),
                'creditSummary'      => null,
                'currency'           => $currency,
            ]);
        }

        $outlets = DB::table('pharmacy_group_members as m')
            ->join('facilities as f', 'm.facility_id', '=', 'f.id')
            ->where('m.group_id', $group->id)
            ->whereNull('f.deleted_at')
            ->select(['f.id', 'f.ulid', 'f.facility_name', 'f.county',
                      'f.ward', 'f.facility_status', 'f.network_membership'])
            ->get();

        $outletIds = $outlets->pluck('id');

        $outletCredit = DB::table('facility_credit_accounts')
            ->whereIn('facility_id', $outletIds)
            ->select(['facility_id', 'account_status', 'suspension_reason'])
            ->get()->keyBy('facility_id');

        $outletOrders = DB::table('orders')
            ->whereIn('retail_facility_id', $outletIds)
            ->whereMonth('created_at', now()->month)
            ->whereNull('deleted_at')
            ->selectRaw('retail_facility_id, COUNT(*) as order_count, SUM(total_amount) as gmv')
            ->groupBy('retail_facility_id')
            ->get()->keyBy('retail_facility_id');

        $authorisedOutletIds = DB::table('facility_authorised_placers')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereIn('facility_id', $outletIds)
            ->pluck('facility_id');

        $creditSummary = DB::table('facility_credit_accounts')
            ->whereIn('facility_id', $outletIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN account_status='ACTIVE'    THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN account_status='SUSPENDED' THEN 1 ELSE 0 END) as suspended_count
            ")
            ->first();

        return view('group.dashboard', compact(
            'group', 'outlets', 'outletCredit', 'outletOrders',
            'creditSummary', 'currency', 'authorisedOutletIds'
        ));
    }
}
