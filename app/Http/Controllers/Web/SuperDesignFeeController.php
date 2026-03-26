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

class SuperDesignFeeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasRole('super_admin')) {
            return redirect('/dashboard');
        }
        $currency = CurrencyConfig::get();
        $tranches = DB::table('credit_tranches')->orderBy('id')->get();
        return view('super.design-fee', compact('tranches', 'currency'));
    }

    public function release(Request $request, $tranche)
    {
        if (! Auth::user()->hasRole('super_admin')) abort(403);
        // Business logic wired by Datanav — stub triggers confirmation
        return back()->with('success', 'Design fee release initiated for tranche ID ' . $tranche . '.');
    }
}
