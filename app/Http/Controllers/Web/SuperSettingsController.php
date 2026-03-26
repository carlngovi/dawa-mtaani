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

class SuperSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['super_admin', 'technical_admin'])) {
            return redirect('/dashboard');
        }
        $settings = DB::table('system_settings')->orderBy('key')->get()->keyBy('key');
        $currency = CurrencyConfig::get();
        return view('super.settings', compact('settings', 'currency'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['super_admin', 'technical_admin'])) {
            abort(403);
        }
        $allowed = [
            'currency_iso_code', 'currency_symbol', 'currency_decimal_places',
            'grant_exchange_rate', 'grant_base_currency', 'display_timezone',
        ];
        foreach ($request->only($allowed) as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
        CurrencyConfig::clearCache();
        return back()->with('success', 'Settings saved. Changes take effect immediately.');
    }
}
