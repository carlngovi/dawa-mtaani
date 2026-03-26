<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GroupOrderController
 *
 * group_owner, place group order
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupOrderController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $currency = CurrencyConfig::get();

        $group = DB::table('pharmacy_groups')
            ->where('group_owner_user_id', $user->id)
            ->first();

        if (! $group) return redirect('/group/dashboard');

        $authorisedOutlets = DB::table('facility_authorised_placers as p')
            ->join('facilities as f', 'p.facility_id', '=', 'f.id')
            ->join('pharmacy_group_members as m', 'f.id', '=', 'm.facility_id')
            ->where('m.group_id', $group->id)
            ->where('p.user_id', $user->id)
            ->where('p.is_active', true)
            ->whereNull('f.deleted_at')
            ->select(['f.id', 'f.ulid', 'f.facility_name', 'f.county',
                      'f.facility_status', 'f.network_membership'])
            ->get();

        $selectedOutlet = null;
        $products       = collect();

        if ($request->filled('outlet')) {
            $selectedOutlet = DB::table('facilities')
                ->where('ulid', $request->outlet)
                ->first();

            if ($selectedOutlet) {
                $products = DB::table('wholesale_price_lists as p')
                    ->join('products as pr', 'p.product_id', '=', 'pr.id')
                    ->where('p.is_active', true)
                    ->where('p.stock_status', '!=', 'OUT_OF_STOCK')
                    ->whereNull('pr.deleted_at')
                    ->select(['pr.id', 'pr.product_name', 'pr.pack_size',
                              'pr.therapeutic_category', 'p.unit_price', 'p.stock_status'])
                    ->orderBy('pr.therapeutic_category')
                    ->orderBy('pr.product_name')
                    ->get()
                    ->groupBy('therapeutic_category');
            }
        }

        return view('group.order', compact(
            'authorisedOutlets', 'selectedOutlet', 'products', 'currency', 'group'
        ));
    }
}
