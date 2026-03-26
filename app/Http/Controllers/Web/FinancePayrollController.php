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
 * FinancePayrollController
 *
 * shared_accountant, payroll view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinancePayrollController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['shared_accountant', 'admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        $currency    = CurrencyConfig::get();
        $commissions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $unpaidTotal = 0;

        if (Schema::hasTable('recruiter_ledger_entries')) {
            $commissions = DB::table('recruiter_ledger_entries as rl')
                ->join('recruiter_agents as ra', 'rl.agent_id', '=', 'ra.id')
                ->join('recruiter_firms as rf',  'ra.firm_id',  '=', 'rf.id')
                ->select(['rl.id', 'rl.amount_kes', 'rl.entry_type', 'rl.created_at',
                          'ra.agent_name', 'rf.firm_name'])
                ->orderBy('rl.created_at', 'desc')
                ->paginate(20);

            $unpaidTotal = DB::table('recruiter_ledger_entries')
                ->where('entry_type', 'COMMISSION')
                ->sum('amount_kes');
        }

        return view('finance.payroll', compact('commissions', 'unpaidTotal', 'currency'));
    }
}
