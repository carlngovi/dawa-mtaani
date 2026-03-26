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

class SuperFeesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['super_admin', 'technical_admin'])) {
            return redirect('/dashboard');
        }
        $currency = CurrencyConfig::get();
        $fees     = collect();
        if (Schema::hasTable('platform_fee_config')) {
            $fees = DB::table('platform_fee_config')->orderBy('fee_type')->get();
        }
        return view('super.fees', compact('fees', 'currency'));
    }
}
