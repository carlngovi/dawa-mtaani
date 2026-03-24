<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use App\Services\OrderPlacementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderPlacementService $orderService
    ) {}

    // POST /api/v1/orders
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'          => 'required|integer|exists:facilities,id',
            'order_type'           => 'required|in:CREDIT,CASH,MIXED,OFF_NETWORK_CASH',
            'is_group_order'       => 'boolean',
            'notes'                => 'nullable|string|max:1000',
            'lines'                => 'required|array|min:1',
            'lines.*.product_id'   => 'required|integer|exists:products,id',
            'lines.*.price_list_id' => 'required|integer|exists:wholesale_price_lists,id',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.payment_type' => 'required|in:CREDIT,CASH,OFF_NETWORK_CASH',
            'lines.*.delivery_facility_id' => 'nullable|integer|exists:facilities,id',
        ]);

        try {
            $result = $this->orderService->placeOrder(
                facilityId: $validated['facility_id'],
                placedByUserId: $request->user()->id,
                lines: $validated['lines'],
                orderType: $validated['order_type'],
                sourceChannel: 'WEB',
                isGroupOrder: $validated['is_group_order'] ?? false,
                notes: $validated['notes'] ?? null
            );

            $currency = CurrencyConfig::get();

            return response()->json([
                'message'      => 'Order placed successfully.',
                'order'        => $result,
                'currency'     => $currency['symbol'],
            ], 201);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // POST /api/v1/orders/sync (batch offline submission)
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders'                          => 'required|array',
            'orders.*.idempotency_key'        => 'required|string',
            'orders.*.facility_id'            => 'required|integer',
            'orders.*.order_type'             => 'required|string',
            'orders.*.lines'                  => 'required|array',
            'orders.*.lines.*.product_id'     => 'required|integer',
            'orders.*.lines.*.price_list_id'  => 'required|integer',
            'orders.*.lines.*.quantity'       => 'required|integer|min:1',
            'orders.*.lines.*.payment_type'   => 'required|string',
        ]);

        $results = [];

        foreach ($validated['orders'] as $orderData) {
            $key = $orderData['idempotency_key'];

            // Check idempotency
            $existing = DB::table('orders')
                ->where('ulid', $key)
                ->first();

            if ($existing) {
                $results[] = [
                    'idempotency_key' => $key,
                    'status'          => 'already_processed',
                    'order_ulid'      => $existing->ulid,
                ];
                continue;
            }

            try {
                $result = $this->orderService->placeOrder(
                    facilityId: $orderData['facility_id'],
                    placedByUserId: $request->user()->id,
                    lines: $orderData['lines'],
                    orderType: $orderData['order_type'],
                    sourceChannel: 'OFFLINE_QR',
                    isGroupOrder: false,
                    notes: $orderData['notes'] ?? null
                );

                $results[] = [
                    'idempotency_key' => $key,
                    'status'          => 'accepted',
                    'order_ulid'      => $result['ulid'],
                ];

            } catch (\Throwable $e) {
                $results[] = [
                    'idempotency_key' => $key,
                    'status'          => 'failed',
                    'error'           => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'processed' => count($results),
            'results'   => $results,
        ]);
    }

    // GET /api/v1/orders
    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $query = DB::table('orders')
            ->where('retail_facility_id', $facilityId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);

        return response()->json($orders);
    }

    // GET /api/v1/orders/{ulid}
    public function show(Request $request, string $ulid): JsonResponse
    {
        $order = DB::table('orders as o')
            ->where('o.ulid', $ulid)
            ->whereNull('o.deleted_at')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $lines = DB::table('order_lines as ol')
            ->join('products as p', 'ol.product_id', '=', 'p.id')
            ->join('facilities as wf', 'ol.wholesale_facility_id', '=', 'wf.id')
            ->where('ol.order_id', $order->id)
            ->select([
                'ol.*',
                'p.generic_name',
                'p.sku_code',
                'wf.facility_name as wholesale_facility_name',
            ])
            ->get();

        return response()->json([
            'order' => $order,
            'lines' => $lines,
        ]);
    }

    // DELETE /api/v1/orders/{ulid}
    public function cancel(Request $request, string $ulid): JsonResponse
    {
        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->where('retail_facility_id', $request->user()->facility_id)
            ->whereNull('deleted_at')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->status !== 'PENDING') {
            return response()->json([
                'message' => 'Only PENDING orders can be cancelled.',
            ], 422);
        }

        DB::table('orders')
            ->where('ulid', $ulid)
            ->update([
                'status'     => 'CANCELLED',
                'updated_at' => Carbon::now('UTC'),
            ]);

        return response()->json(['message' => 'Order cancelled.']);
    }
}
