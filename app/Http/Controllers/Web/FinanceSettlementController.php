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
 * FinanceSettlementController
 *
 * shared_accountant, settlement records view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceSettlementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['shared_accountant', 'admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        $currency = CurrencyConfig::get();
        $records  = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        $summary  = null;

        if (Schema::hasTable('settlement_records')) {
            $records = DB::table('settlement_records')
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->orderBy('created_at', 'desc')
                ->paginate(15)->withQueryString();

            $summary = DB::table('settlement_records')
                ->whereMonth('created_at', now()->month)
                ->selectRaw('COUNT(*) as cycle_count,
                             SUM(gross_amount) as monthly_gross,
                             SUM(net_amount)   as monthly_net')
                ->first();
        }

        return view('finance.settlement', compact('records', 'summary', 'currency'));
    }
}
