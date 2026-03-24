<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\CourierProviderInterface;
use App\DTOs\DispatchNotification;
use App\Http\Controllers\Controller;
use App\Services\BusinessMetricCollector;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WholesaleOrderController extends Controller
{
    // GET /api/v1/wholesale/orders
    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $query = DB::table('orders as o')
            ->join('facilities as rf', 'o.retail_facility_id', '=', 'rf.id')
            ->whereNull('o.deleted_at')
            ->where(function ($q) use ($facilityId) {
                // Orders that have at least one line from this wholesale facility
                $q->whereExists(function ($sub) use ($facilityId) {
                    $sub->select(DB::raw(1))
                        ->from('order_lines')
                        ->whereColumn('order_lines.order_id', 'o.id')
                        ->where('order_lines.wholesale_facility_id', $facilityId);
                });
            })
            ->select([
                'o.id',
                'o.ulid',
                'o.status',
                'o.total_amount',
                'o.order_type',
                'o.source_channel',
                'o.submitted_at',
                'o.created_at',
                'rf.facility_name as retail_facility_name',
                'rf.county',
                'rf.ward',
            ])
            ->orderBy('o.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('o.status', $request->status);
        }

        $orders = $query->paginate(20);

        return response()->json($orders);
    }

    // PATCH /api/v1/wholesale/orders/{ulid}/status
    public function updateStatus(Request $request, string $ulid): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:CONFIRMED,PICKING,PACKED',
        ]);

        $facilityId = $request->user()->facility_id;

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Verify this wholesale facility has lines in this order
        $hasLines = DB::table('order_lines')
            ->where('order_id', $order->id)
            ->where('wholesale_facility_id', $facilityId)
            ->exists();

        if (! $hasLines) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        DB::table('orders')
            ->where('ulid', $ulid)
            ->update([
                'status'     => $validated['status'],
                'updated_at' => Carbon::now('UTC'),
            ]);

        BusinessMetricCollector::record('orders_status_updated', 1);

        return response()->json([
            'message' => 'Order status updated.',
            'status'  => $validated['status'],
        ]);
    }

    // POST /api/v1/wholesale/orders/{ulid}/dispatch
    public function dispatch(Request $request, string $ulid): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $order = DB::table('orders')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->status !== 'PACKED') {
            return response()->json([
                'message' => 'Order must be PACKED before dispatch.',
            ], 422);
        }

        return $this->processDispatch($request, $order->id, null, $facilityId);
    }

    // POST /api/v1/wholesale/orders/split/{splitId}/dispatch
    public function dispatchSplit(Request $request, int $splitId): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $split = DB::table('order_delivery_splits')
            ->where('id', $splitId)
            ->first();

        if (! $split) {
            return response()->json(['message' => 'Split line not found.'], 404);
        }

        return $this->processDispatch($request, null, $splitId, $facilityId);
    }

    private function processDispatch(
        Request $request,
        ?int $orderId,
        ?int $splitLineId,
        int $facilityId
    ): JsonResponse {
        $now = Carbon::now('UTC');

        return DB::transaction(function () use (
            $request, $orderId, $splitLineId, $facilityId, $now
        ) {
            // Load courier assignment
            $courierAssignment = DB::table('courier_assignments')
                ->where('order_id', $orderId)
                ->where('split_line_id', $splitLineId)
                ->first();

            // Get facility details for dispatch notification
            $facility = DB::table('facilities')
                ->where('id', $facilityId)
                ->first();

            $deliveryAddress = '';
            if ($orderId) {
                $order = DB::table('orders')->where('id', $orderId)->first();
                $retailFacility = DB::table('facilities')
                    ->where('id', $order->retail_facility_id)
                    ->first();
                $deliveryAddress = $retailFacility?->physical_address ?? '';
            }

            // Create dispatch trigger record
            $triggerId = DB::table('dispatch_triggers')->insertGetId([
                'order_id'                  => $orderId,
                'split_line_id'             => $splitLineId,
                'triggered_by_facility_id'  => $facilityId,
                'triggered_by_user_id'      => $request->user()->id,
                'triggered_at'              => $now,
                'courier_facility_id'       => $courierAssignment?->courier_facility_id ?? null,
                'created_at'                => $now,
                'updated_at'                => $now,
            ]);

            // Notify courier if assignment exists
            if ($courierAssignment) {
                try {
                    $courierService = app()->make(
                        $courierAssignment->assigned_courier_service
                    );

                    $notification = new DispatchNotification(
                        orderId: (string) ($orderId ?? $splitLineId),
                        orderReference: $courierAssignment->courier_reference ?? '',
                        facilityName: $facility?->facility_name ?? '',
                        deliveryAddress: $deliveryAddress,
                        latitude: $facility?->latitude,
                        longitude: $facility?->longitude,
                    );

                    $courierService->notifyDispatch($notification);

                    DB::table('dispatch_triggers')
                        ->where('id', $triggerId)
                        ->update(['courier_notified_at' => $now]);

                } catch (\Throwable $e) {
                    Log::warning('WholesaleOrderController: courier notification failed', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update order/split status to DISPATCHED
            if ($orderId) {
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update(['status' => 'DISPATCHED', 'updated_at' => $now]);
            } else {
                DB::table('order_delivery_splits')
                    ->where('id', $splitLineId)
                    ->update(['status' => 'DISPATCHED', 'updated_at' => $now]);
            }

            // Audit log
            DB::table('audit_logs')->insert([
                'facility_id'    => $facilityId,
                'user_id'        => $request->user()->id,
                'action'         => 'order_dispatched',
                'model_type'     => $orderId ? 'Order' : 'OrderDeliverySplit',
                'model_id'       => $orderId ?? $splitLineId,
                'payload_after'  => json_encode([
                    'status'       => 'DISPATCHED',
                    'trigger_id'   => $triggerId,
                    'courier'      => $courierAssignment?->assigned_courier_service,
                ]),
                'ip_address'     => $request->ip() ?? '0.0.0.0',
                'created_at'     => $now,
            ]);

            BusinessMetricCollector::record('orders_dispatched', 1);

            return response()->json([
                'message'    => 'Order dispatched.',
                'trigger_id' => $triggerId,
            ], 201);
        });
    }
}
