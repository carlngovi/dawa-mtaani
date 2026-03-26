<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetailPosController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $facility = null;
        if ($user->facility_id) {
            $facility = DB::table('facilities')
                ->where('id', $user->facility_id)
                ->first();
        }

        $products = DB::table('products')
            ->where('is_active', true)
            ->orderBy('generic_name')
            ->select(['id', 'sku_code', 'generic_name', 'brand_name', 'unit_size'])
            ->get();

        $recentSales = collect();
        $todayCount  = 0;

        if ($facility && Schema::hasTable('dispensing_entries')) {
            $recentSales = DB::table('dispensing_entries as d')
                ->join('products as p', 'd.product_id', '=', 'p.id')
                ->where('d.facility_id', $facility->id)
                ->select([
                    'd.id', 'd.quantity', 'd.dispensed_at',
                    'p.generic_name', 'p.sku_code', 'p.unit_size',
                ])
                ->orderBy('d.dispensed_at', 'desc')
                ->limit(25)
                ->get();

            $todayCount = DB::table('dispensing_entries')
                ->where('facility_id', $facility->id)
                ->whereDate('dispensed_at', today())
                ->count();
        }

        return view('retail.pos', compact(
            'facility', 'products', 'recentSales', 'todayCount', 'currency'
        ));
    }
}
