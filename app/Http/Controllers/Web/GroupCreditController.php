<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GroupCreditController
 *
 * group_owner, consolidated credit view (read-only)
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupCreditController extends Controller
{
    public function index()
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

        $creditAccounts = DB::table('facility_credit_accounts as ca')
            ->join('facilities as f',      'ca.facility_id', '=', 'f.id')
            ->join('credit_tranches as ct', 'ca.tranche_id',  '=', 'ct.id')
            ->whereIn('ca.facility_id', $outletIds)
            ->select(['ca.account_status', 'ca.suspension_reason', 'ca.approved_at',
                      'f.facility_name', 'f.county',
                      'ct.name as tranche_name', 'ct.ceiling_amount as credit_limit_kes'])
            ->get();

        $recentEvents = DB::table('credit_events as e')
            ->join('facility_credit_accounts as ca', 'e.credit_account_id', '=', 'ca.id')
            ->join('facilities as f', 'ca.facility_id', '=', 'f.id')
            ->whereIn('ca.facility_id', $outletIds)
            ->select(['e.event_type', 'e.amount', 'e.occurred_at as created_at', 'f.facility_name'])
            ->orderBy('e.occurred_at', 'desc')
            ->limit(20)
            ->get();

        return view('group.credit', compact('creditAccounts', 'recentEvents', 'currency', 'group'));
    }
}
