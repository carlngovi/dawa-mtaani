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
 * FinanceSweepsController
 *
 * shared_accountant, fund sweeps view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceSweepsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['shared_accountant', 'admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        $currency   = CurrencyConfig::get();
        $sweeps     = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        $monthTotal = 0;

        if (Schema::hasTable('nila_settlement_sweeps')) {
            $sweeps = DB::table('nila_settlement_sweeps')
                ->orderBy('sweep_date', 'desc')
                ->paginate(15);

            $monthTotal = DB::table('nila_settlement_sweeps')
                ->whereMonth('sweep_date', now()->month)
                ->sum('net_amount');
        }

        return view('finance.sweeps', compact('sweeps', 'monthTotal', 'currency'));
    }
}
