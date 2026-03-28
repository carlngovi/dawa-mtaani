<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogisticsDeliveriesController extends Controller
{
    public function index(Request $request)
    {
        $currency = CurrencyConfig::get();

        // ── B2B orders (retail → wholesale) ──────────────────────────
        $b2b = DB::table('orders as o')
            ->join('facilities as retail', 'o.retail_facility_id', '=', 'retail.id')
            ->leftJoin('delivery_confirmations as dc', 'dc.order_id', '=', 'o.id')
            ->whereNull('o.deleted_at')
            ->whereIn('o.status', ['CONFIRMED', 'PACKED', 'DISPATCHED', 'DELIVERED'])
            ->when($request->filled('county'), fn($q) => $q->where('retail.county', $request->county))
            ->when($request->filled('status'), fn($q) => $q->where('o.status', $request->status))
            ->select([
                'o.id as order_id',
                DB::raw("'B2B' as order_type"),
                'dc.id as dc_id', 'dc.confirmation_type', 'dc.confirmed_at',
                'o.ulid as order_ulid', 'o.status', 'o.total_amount',
                'o.created_at', 'o.updated_at',
                'retail.facility_name', 'retail.county', 'retail.ward',
                DB::raw('NULL as customer_name'),
                'o.delivery_address', 'o.delivery_lat', 'o.delivery_lng',
            ]);

        // ── B2C customer orders ──────────────────────────────────────
        $b2c = DB::table('customer_orders as po')
            ->join('facilities as f', 'po.facility_id', '=', 'f.id')
            ->whereIn('po.status', ['CONFIRMED', 'PREPARING', 'READY', 'COLLECTED'])
            ->when($request->filled('county'), fn($q) => $q->where('f.county', $request->county))
            ->when($request->filled('status'), fn($q) => $q->where('po.status', $request->status))
            ->select([
                'po.id as order_id',
                DB::raw("'B2C' as order_type"),
                DB::raw('NULL as dc_id'), DB::raw('NULL as confirmation_type'), DB::raw('NULL as confirmed_at'),
                'po.ulid as order_ulid', 'po.status', 'po.total_amount',
                'po.created_at', 'po.updated_at',
                'f.facility_name', 'f.county', 'f.ward',
                'po.customer_name',
                'po.delivery_address', 'po.delivery_lat', 'po.delivery_lng',
            ]);

        // ── Combined & paginated ─────────────────────────────────────
        $deliveries = $b2b->unionAll($b2c)
            ->orderByRaw("CASE
                WHEN status='DISPATCHED' THEN 0
                WHEN status='PACKED'     THEN 1
                WHEN status='CONFIRMED'  THEN 2
                WHEN status='PREPARING'  THEN 3
                ELSE 4 END")
            ->orderBy('updated_at', 'asc')
            ->paginate(25)->withQueryString();

        // ── Stats ────────────────────────────────────────────────────
        $b2bPending = DB::table('orders')
            ->whereIn('status', ['CONFIRMED', 'PACKED', 'DISPATCHED'])
            ->whereNull('deleted_at')->count();
        $b2cPending = DB::table('customer_orders')
            ->whereIn('status', ['CONFIRMED', 'PREPARING', 'READY'])->count();

        $stats = [
            'awaiting_delivery' => $b2bPending + $b2cPending,
            'delivered_today'   => DB::table('delivery_confirmations')->whereDate('confirmed_at', today())->count(),
            'open_disputes'     => DB::table('delivery_disputes')->where('status', 'OPEN')->count(),
        ];

        $counties = DB::table('facilities')
            ->whereNotNull('county')->whereNull('deleted_at')
            ->distinct()->orderBy('county')->pluck('county');

        // Build map pins from deliveries that have GPS coordinates
        $mapPins = collect($deliveries->items())->filter(fn($d) => $d->delivery_lat && $d->delivery_lng)->map(fn($d) => [
            'ulid'     => substr($d->order_ulid, -8),
            'lat'      => (float) $d->delivery_lat,
            'lng'      => (float) $d->delivery_lng,
            'type'     => $d->order_type,
            'status'   => $d->status,
            'name'     => $d->customer_name ?? $d->facility_name,
            'address'  => $d->delivery_address,
            'amount'   => (float) $d->total_amount,
        ])->values();

        $mapPinsJson   = $mapPins->toJson();
        $googleMapsKey = config('services.google_maps.key');

        return view('logistics.deliveries', compact('deliveries', 'stats', 'counties', 'currency', 'mapPinsJson', 'googleMapsKey'));
    }
}
