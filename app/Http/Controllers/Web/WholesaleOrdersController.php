<?php

namespace App\Http\Controllers\Web;

use App\Contracts\CourierProviderInterface;
use App\DTOs\DispatchNotification;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WholesaleOrdersController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $currency = CurrencyConfig::get();

        $query = DB::table('orders as o')
            ->join('facilities as rf', 'o.retail_facility_id', '=', 'rf.id')
            ->whereNull('o.deleted_at')
            ->whereExists(function ($sub) use ($facilityId) {
                $sub->select(DB::raw(1))
                    ->from('order_lines')
                    ->whereColumn('order_lines.order_id', 'o.id')
                    ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->when($request->filled('status'), fn($q) => $q->where('o.status', $request->status))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('o.ulid', 'like', '%' . $request->search . '%')
                  ->orWhere('rf.facility_name', 'like', '%' . $request->search . '%');
            }))
            ->select(['o.*', 'rf.facility_name as retail_name', 'rf.county'])
            ->orderByRaw("CASE WHEN o.status='PENDING' THEN 0 WHEN o.status='CONFIRMED' THEN 1 WHEN o.status='PACKED' THEN 2 ELSE 3 END")
            ->orderBy('o.created_at', 'asc');

        $orders = $query->paginate(25)->withQueryString();

        // Get line counts per order
        $orderIds = collect($orders->items())->pluck('id')->filter()->toArray();
        $lineCounts = [];
        if ($orderIds) {
            $lineCounts = DB::table('order_lines')
                ->whereIn('order_id', $orderIds)
                ->where('wholesale_facility_id', $facilityId)
                ->selectRaw('order_id, COUNT(*) as cnt')
                ->groupBy('order_id')
                ->pluck('cnt', 'order_id')
                ->toArray();
        }

        $scopedCount = fn($status) => DB::table('orders as o')
            ->whereNull('o.deleted_at')
            ->where('o.status', $status)
            ->whereExists(function ($sub) use ($facilityId) {
                $sub->select(DB::raw(1))->from('order_lines')
                    ->whereColumn('order_lines.order_id', 'o.id')
                    ->where('order_lines.wholesale_facility_id', $facilityId);
            })->count();

        $counts = [
            'pending'    => $scopedCount('PENDING'),
            'confirmed'  => $scopedCount('CONFIRMED'),
            'packed'     => $scopedCount('PACKED'),
            'dispatched' => DB::table('orders as o')
                ->whereNull('o.deleted_at')
                ->where('o.status', 'DISPATCHED')
                ->whereDate('o.updated_at', today())
                ->whereExists(function ($sub) use ($facilityId) {
                    $sub->select(DB::raw(1))->from('order_lines')
                        ->whereColumn('order_lines.order_id', 'o.id')
                        ->where('order_lines.wholesale_facility_id', $facilityId);
                })->count(),
        ];

        return view('wholesale.orders', compact('orders', 'currency', 'counts', 'lineCounts'));
    }

    public function show(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $currency = CurrencyConfig::get();

        $order = DB::table('orders as o')
            ->join('facilities as rf', 'o.retail_facility_id', '=', 'rf.id')
            ->where('o.ulid', $ulid)
            ->whereNull('o.deleted_at')
            ->select(['o.*', 'rf.facility_name as retail_name', 'rf.county as retail_county',
                       'rf.phone as retail_phone', 'rf.ppb_licence_number as retail_ppb',
                       'rf.physical_address as retail_address'])
            ->first();

        abort_if(! $order, 404);

        $lines = DB::table('order_lines as ol')
            ->join('products as p', 'p.id', '=', 'ol.product_id')
            ->where('ol.order_id', $order->id)
            ->where('ol.wholesale_facility_id', $facilityId)
            ->select(['ol.*', 'p.generic_name', 'p.brand_name', 'p.sku_code', 'p.unit_size'])
            ->get();

        $courier = DB::table('courier_assignments')
            ->where('order_id', $order->id)
            ->first();

        $auditLogs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->where('audit_logs.model_type', 'Order')
            ->where('audit_logs.model_id', $order->id)
            ->orWhere(function ($q) use ($order) {
                $q->where('audit_logs.model_type', 'App\Models\Order')
                  ->where('audit_logs.model_id', $order->id);
            })
            ->select(['audit_logs.*', 'users.name as actor_name'])
            ->orderBy('audit_logs.created_at', 'desc')
            ->limit(20)
            ->get();

        return view('wholesale.orders-show', compact('order', 'lines', 'courier', 'auditLogs', 'currency'));
    }

    public function confirm(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $order = Order::where('ulid', $ulid)->whereNull('deleted_at')->firstOrFail();

        if ($order->status !== 'PENDING') {
            return back()->with('error', 'Order is not in PENDING status.');
        }

        DB::transaction(function () use ($order, $request, $facilityId) {
            $order->update([
                'status'       => 'CONFIRMED',
                'confirmed_at' => now(),
            ]);

            DB::table('audit_logs')->insert([
                'facility_id'    => $facilityId,
                'user_id'        => $request->user()->id,
                'action'         => 'ORDER_CONFIRMED',
                'model_type'     => 'App\Models\Order',
                'model_id'       => $order->id,
                'payload_before' => json_encode(['status' => 'PENDING']),
                'payload_after'  => json_encode(['status' => 'CONFIRMED']),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'created_at'     => now(),
            ]);
        });

        try {
            $retailPhone = DB::table('facilities')->where('id', $order->retail_facility_id)->value('phone');
            if ($retailPhone) {
                $this->sendSms($retailPhone, "Your order " . substr($order->ulid, -8) . " has been confirmed by NILA Pharmaceuticals and is being prepared.");
            }
        } catch (\Throwable $e) {
            Log::warning('SMS failed after order confirm', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return redirect("/wholesale/orders/{$ulid}")->with('success', 'Order confirmed successfully.');
    }

    public function pack(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $order = Order::where('ulid', $ulid)->whereNull('deleted_at')->firstOrFail();

        if ($order->status !== 'CONFIRMED') {
            return back()->with('error', 'Order must be CONFIRMED before packing.');
        }

        DB::transaction(function () use ($order, $request, $facilityId) {
            $order->update(['status' => 'PACKED']);

            DB::table('audit_logs')->insert([
                'facility_id'    => $facilityId,
                'user_id'        => $request->user()->id,
                'action'         => 'ORDER_PACKED',
                'model_type'     => 'App\Models\Order',
                'model_id'       => $order->id,
                'payload_before' => json_encode(['status' => 'CONFIRMED']),
                'payload_after'  => json_encode(['status' => 'PACKED']),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'created_at'     => now(),
            ]);
        });

        return redirect("/wholesale/orders/{$ulid}")->with('success', 'Order marked as packed. Ready for dispatch.');
    }

    public function dispatch(Request $request, string $ulid)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $order = Order::where('ulid', $ulid)->whereNull('deleted_at')->firstOrFail();

        if ($order->status !== 'PACKED') {
            return back()->with('error', 'Order must be PACKED before dispatch.');
        }

        $trackingRef = $this->processDispatch($order, $facilityId, $request);

        if ($trackingRef === false) {
            return back()->with('error', 'Dispatch failed. Please try again.');
        }

        return redirect('/wholesale/orders')->with('success', "Order dispatched. Tracking: {$trackingRef}");
    }

    public function bulkDispatch(Request $request)
    {
        $facilityId = Auth::user()->facility_id;
        abort_unless(Auth::user()->hasRole('wholesale_facility'), 403);

        $request->validate([
            'order_ulids'   => 'required|array|min:1|max:50',
            'order_ulids.*' => 'string',
        ]);

        $orders = Order::whereIn('ulid', $request->order_ulids)
            ->where('status', 'PACKED')
            ->whereNull('deleted_at')
            ->get();

        $successes = 0;
        $failures  = 0;

        foreach ($orders as $order) {
            $result = $this->processDispatch($order, $facilityId, $request);
            $result !== false ? $successes++ : $failures++;
        }

        $msg = "{$successes} order(s) dispatched successfully.";
        if ($failures > 0) {
            $msg .= " {$failures} failed.";
        }

        return redirect('/wholesale/orders?status=DISPATCHED')->with('success', $msg);
    }

    private function processDispatch(Order $order, int $facilityId, Request $request): string|false
    {
        $now = Carbon::now('UTC');

        try {
            return DB::transaction(function () use ($order, $facilityId, $request, $now) {
                $retailFacility = DB::table('facilities')
                    ->where('id', $order->retail_facility_id)
                    ->first();

                $trackingRef = 'SGA-' . strtoupper(substr($order->ulid, -8)) . '-' . $now->format('dHi');

                // Create dispatch trigger
                DB::table('dispatch_triggers')->insert([
                    'order_id'                 => $order->id,
                    'triggered_by_facility_id' => $facilityId,
                    'triggered_by_user_id'     => $request->user()->id,
                    'triggered_at'             => $now,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ]);

                // Try SGA notification
                try {
                    $courier = app(CourierProviderInterface::class);
                    $notification = new DispatchNotification(
                        orderId: (string) $order->id,
                        orderReference: $order->ulid,
                        facilityName: $retailFacility->facility_name ?? '',
                        deliveryAddress: $retailFacility->physical_address ?? '',
                        latitude: $retailFacility->latitude ?? null,
                        longitude: $retailFacility->longitude ?? null,
                        courierReference: $trackingRef,
                    );
                    $courier->notifyDispatch($notification);
                } catch (\Throwable $e) {
                    Log::warning('SGA courier notification failed — using generated tracking ref', [
                        'order_id' => $order->id, 'error' => $e->getMessage(),
                    ]);
                }

                // Create courier assignment
                DB::table('courier_assignments')->insert([
                    'order_id'                  => $order->id,
                    'assigned_courier_service'  => 'App\Services\Integrations\SgaLogisticsService',
                    'assigned_by'               => $request->user()->id,
                    'assigned_at'               => $now,
                    'courier_reference'         => $trackingRef,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]);

                $order->update(['status' => 'DISPATCHED']);

                DB::table('audit_logs')->insert([
                    'facility_id'    => $facilityId,
                    'user_id'        => $request->user()->id,
                    'action'         => 'ORDER_DISPATCHED',
                    'model_type'     => 'App\Models\Order',
                    'model_id'       => $order->id,
                    'payload_after'  => json_encode(['status' => 'DISPATCHED', 'tracking' => $trackingRef]),
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'created_at'     => $now,
                ]);

                try {
                    if ($retailFacility->phone) {
                        $this->sendSms($retailFacility->phone,
                            "Your order " . substr($order->ulid, -8) . " has been dispatched. Tracking: {$trackingRef}."
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('SMS failed after dispatch', ['order_id' => $order->id]);
                }

                return $trackingRef;
            });
        } catch (\Throwable $e) {
            Log::error('Dispatch transaction failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendSms(string $phone, string $message): void
    {
        if (class_exists(\AfricasTalking\SDK::class)) {
            $at = new \AfricasTalking\SDK(config('services.africastalking.username'), config('services.africastalking.api_key'));
            $at->sms()->send(['to' => $phone, 'message' => $message]);
        }
    }
}
