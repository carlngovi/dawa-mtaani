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
 * FinanceCreditController
 *
 * shared_accountant, credit positions view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceCreditController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['shared_accountant', 'admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        $currency = CurrencyConfig::get();

        $accounts = DB::table('facility_credit_accounts as ca')
            ->join('facilities as f',      'ca.facility_id', '=', 'f.id')
            ->join('credit_tranches as ct', 'ca.tranche_id',  '=', 'ct.id')
            ->whereNull('f.deleted_at')
            ->when($request->filled('status'), fn($q) => $q->where('ca.account_status', $request->status))
            ->when($request->filled('county'), fn($q) => $q->where('f.county', $request->county))
            ->select([
                'ca.account_status', 'ca.suspension_reason', 'ca.approved_at',
                'f.facility_name', 'f.county', 'f.network_membership',
                'ct.tranche_name', 'ct.credit_limit_kes',
            ])
            ->orderBy('f.county')
            ->orderBy('f.facility_name')
            ->paginate(25)->withQueryString();

        $summary = DB::table('facility_credit_accounts')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN account_status = 'ACTIVE'             THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN account_status = 'SUSPENDED'          THEN 1 ELSE 0 END) as suspended_count,
                SUM(CASE WHEN account_status = 'PENDING_ASSESSMENT' THEN 1 ELSE 0 END) as pending_count
            ")
            ->first();

        $counties = DB::table('facilities')
            ->whereNotNull('county')
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('county')
            ->pluck('county');

        return view('finance.credit', compact('accounts', 'summary', 'currency', 'counties'));
    }
}
