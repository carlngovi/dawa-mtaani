<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreBrowseController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $query = DB::table('products')
            ->where('is_active', true)
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('generic_name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand_name', 'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('category'), fn($q) => $q->where('therapeutic_category', $request->category))
            ->orderBy('generic_name');

        $products = $query->paginate(24)->withQueryString();

        $categories = DB::table('products')
            ->where('is_active', true)
            ->whereNotNull('therapeutic_category')
            ->distinct()
            ->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');

        $basketCount = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->join('patient_basket_lines', 'patient_baskets.id', '=', 'patient_basket_lines.basket_id')
            ->sum('patient_basket_lines.quantity');

        // Eligible pharmacies for linking from search results
        $eligibleFacilities = DB::table('online_store_eligible_facilities as osef')
            ->join('facilities as f', 'f.id', '=', 'osef.facility_id')
            ->where('osef.is_active', true)
            ->where('f.facility_status', 'ACTIVE')
            ->whereNull('f.deleted_at')
            ->select(['f.ulid', 'f.facility_name', 'f.county', 'f.network_membership', 'osef.branding_mode'])
            ->orderBy('f.facility_name')
            ->limit(50)
            ->get();

        return view('store.browse', compact('products', 'categories', 'currency', 'basketCount', 'eligibleFacilities'));
    }

    public function storefront(string $facilityUlid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')
            ->where('ulid', $facilityUlid)
            ->where('facility_status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $facility, 404);

        $eligible = DB::table('online_store_eligible_facilities')
            ->where('facility_id', $facility->id)
            ->where('is_active', true)
            ->first();

        abort_if(! $eligible, 404);

        $products = DB::table('facility_stock_status as fss')
            ->join('products as p', 'p.id', '=', 'fss.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.wholesale_facility_id', '=', 'fss.wholesale_facility_id')
                    ->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.is_active', true);
            })
            ->where('fss.wholesale_facility_id', $facility->id)
            ->select([
                'p.id as product_id',
                'p.ulid as product_ulid',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'p.therapeutic_category',
                'wpl.unit_price',
                'fss.stock_status',
            ])
            ->orderBy('p.therapeutic_category')
            ->orderBy('p.generic_name')
            ->get();

        $categories = $products->pluck('therapeutic_category')->filter()->unique()->sort()->values();

        // Load existing basket for this facility
        $basket = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->where('facility_id', $facility->id)
            ->first();

        $basketToken = $basket->session_token ?? null;

        return view('store.storefront', compact(
            'facility', 'eligible', 'products', 'categories', 'currency', 'basketToken', 'user'
        ));
    }

    public function checkout(string $facilityUlid)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $facility = DB::table('facilities')
            ->where('ulid', $facilityUlid)
            ->where('facility_status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $facility, 404);

        $basket = DB::table('patient_baskets')
            ->where('patient_phone', $user->phone ?? '')
            ->where('facility_id', $facility->id)
            ->first();

        if (! $basket) {
            return redirect("/store/{$facilityUlid}")->with('error', 'Your basket is empty.');
        }

        $lines = DB::table('patient_basket_lines as pbl')
            ->join('products as p', 'p.id', '=', 'pbl.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) use ($facility) {
                $join->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.wholesale_facility_id', $facility->id)
                    ->where('wpl.is_active', true);
            })
            ->where('pbl.basket_id', $basket->id)
            ->select([
                'p.generic_name', 'p.brand_name', 'p.unit_size',
                'pbl.quantity', 'wpl.unit_price',
            ])
            ->get()
            ->map(function ($row) {
                $row->line_total = (float) $row->unit_price * $row->quantity;
                return $row;
            });

        if ($lines->isEmpty()) {
            return redirect("/store/{$facilityUlid}")->with('error', 'Your basket is empty.');
        }

        $subtotal = $lines->sum('line_total');

        $platformFeePct = (float) (DB::table('system_settings')
            ->where('key', 'platform_fee_pct')
            ->value('value') ?: 3.00);

        $platformFee = round($subtotal * $platformFeePct / 100, 2);
        $total = round($subtotal + $platformFee, 2);

        return view('store.checkout', compact(
            'facility', 'lines', 'subtotal', 'platformFee', 'platformFeePct', 'total', 'currency', 'user', 'basket'
        ));
    }
}
