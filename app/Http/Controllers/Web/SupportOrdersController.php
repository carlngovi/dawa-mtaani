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
 * SupportOrdersController
 *
 * admin_support, read-only order lookup
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportOrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin_support', 'admin', 'super_admin', 'assistant_admin'])) {
            return redirect('/dashboard');
        }

        $order      = null;
        $orderLines = collect();
        $searched   = false;
        $currency   = CurrencyConfig::get();

        if ($request->filled('ref') && strlen($request->ref) >= 5) {
            $searched = true;
            $ref      = strtoupper(trim($request->ref));

            $order = DB::table('orders as o')
                ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
                ->where('o.ulid', 'like', "%{$ref}%")
                ->whereNull('o.deleted_at')
                ->select([
                    'o.id', 'o.ulid', 'o.status', 'o.total_amount',
                    'o.payment_type', 'o.source_channel', 'o.created_at',
                    'f.facility_name', 'f.county', 'f.phone as facility_phone',
                ])
                ->first();

            if ($order) {
                $orderLines = DB::table('order_lines as ol')
                    ->join('products as p', 'ol.product_id', '=', 'p.id')
                    ->where('ol.order_id', $order->id)
                    ->select([
                        'p.product_name', 'p.pack_size',
                        'ol.quantity', 'ol.unit_price', 'ol.line_total',
                    ])
                    ->get();
            }
        }

        return view('support.orders', compact('order', 'orderLines', 'searched', 'currency'));
    }
}
