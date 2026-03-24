<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ConfirmationAutoTriggerJob;
use App\Services\BusinessMetricCollector;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    // POST /api/v1/deliveries/{orderUlid}/mark-delivered
    public function markDelivered(Request $request, string $orderUlid): JsonResponse
    {
        $validated = $request->validate([
            'pod_photo_path' => 'required|string|max:500',
        ]);

        if (! $request->user()->hasRole(['logistics_facility', 'network_admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $order = DB::table('orders')
            ->where('ulid', $orderUlid)
            ->whereNull('deleted_at')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->processMarkDelivered(
            $request,
            $order->id,
            null,
            $validated['pod_photo_path']
        );
    }

    // POST /api/v1/deliveries/split/{splitLineId}/mark-delivered
    public function markDeliveredSplit(Request $request, int $splitLineId): JsonResponse
    {
        $validated = $request->validate([
            'pod_photo_path' => 'required|string|max:500',
        ]);

        if (! $request->user()->hasRole(['logistics_facility', 'network_admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $split = DB::table('order_delivery_splits')
            ->where('id', $splitLineId)
            ->first();

        if (! $split) {
            return response()->json(['message' => 'Split line not found.'], 404);
        }

        return $this->processMarkDelivered(
            $request,
            null,
            $splitLineId,
            $validated['pod_photo_path']
        );
    }

    // POST /api/v1/deliveries/{orderUlid}/confirm
    public function confirm(Request $request, string $orderUlid): JsonResponse
    {
        $order = DB::table('orders')
            ->where('ulid', $orderUlid)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->processConfirm($request, $order->id, null);
    }

    // POST /api/v1/deliveries/split/{splitLineId}/confirm
    public function confirmSplit(Request $request, int $splitLineId): JsonResponse
    {
        return $this->processConfirm($request, null, $splitLineId);
    }

    // POST /api/v1/deliveries/{orderUlid}/dispute
    public function dispute(Request $request, string $orderUlid): JsonResponse
    {
        $order = DB::table('orders')
            ->where('ulid', $orderUlid)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->processDispute($request, $order->id, null);
    }

    // POST /api/v1/deliveries/split/{splitLineId}/dispute
    public function disputeSplit(Request $request, int $splitLineId): JsonResponse
    {
        return $this->processDispute($request, null, $splitLineId);
    }

    // -------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------

    private function processMarkDelivered(
        Request $request,
        ?int $orderId,
        ?int $splitLineId,
        string $podPhotoPath
    ): JsonResponse {
        $now = Carbon::now('UTC');

        $clockHours = (int) (DB::table('system_settings')
            ->where('key', 'confirmation_clock_hours')
            ->value('value') ?? 72);

        $deadline = $now->copy()->addHours($clockHours);

        $logisticsFacilityId = DB::table('facilities')
            ->where('id', $request->user()->facility_id)
            ->value('id');

        $confirmationId = DB::table('delivery_confirmations')->insertGetId([
            'order_id'                      => $orderId,
            'split_line_id'                 => $splitLineId,
            'logistics_facility_id'         => $logisticsFacilityId,
            'delivered_at'                  => $now,
            'pod_photo_path'                => $podPhotoPath,
            'confirmation_clock_started_at' => $now,
            'confirmation_deadline_at'      => $deadline,
            'created_at'                    => $now,
            'updated_at'                    => $now,
        ]);

        // Update order/split status to DELIVERED
        if ($orderId) {
            DB::table('orders')
                ->where('id', $orderId)
                ->update(['status' => 'DELIVERED', 'updated_at' => $now]);
        } else {
            DB::table('order_delivery_splits')
                ->where('id', $splitLineId)
                ->update(['status' => 'DELIVERED', 'updated_at' => $now]);
        }

        // Schedule auto-trigger job with delay
        ConfirmationAutoTriggerJob::dispatch($confirmationId)
            ->delay($deadline)
            ->onQueue('default');

        // Record business metric
        BusinessMetricCollector::record('deliveries_marked', 1);

        return response()->json([
            'message'              => 'Delivery marked. Confirmation clock started.',
            'confirmation_id'      => $confirmationId,
            'confirmation_deadline' => $deadline->toISOString(),
        ], 201);
    }

    private function processConfirm(
        Request $request,
        ?int $orderId,
        ?int $splitLineId
    ): JsonResponse {
        $now = Carbon::now('UTC');

        $confirmation = DB::table('delivery_confirmations')
            ->where('order_id', $orderId)
            ->where('split_line_id', $splitLineId)
            ->whereNull('confirmed_at')
            ->first();

        if (! $confirmation) {
            return response()->json(['message' => 'Delivery confirmation not found or already confirmed.'], 404);
        }

        DB::table('delivery_confirmations')
            ->where('id', $confirmation->id)
            ->update([
                'confirmed_at'       => $now,
                'confirmed_by'       => $request->user()->id,
                'confirmation_type'  => 'RETAIL_CONFIRMED',
                'updated_at'         => $now,
            ]);

        // Queue payment event
        DB::table('audit_logs')->insert([
            'facility_id'    => $request->user()->facility_id,
            'user_id'        => $request->user()->id,
            'action'         => 'delivery_confirmed',
            'model_type'     => 'DeliveryConfirmation',
            'model_id'       => $confirmation->id,
            'payload_after'  => json_encode(['confirmation_type' => 'RETAIL_CONFIRMED']),
            'ip_address'     => $request->ip() ?? '0.0.0.0',
            'created_at'     => $now,
        ]);

        BusinessMetricCollector::record('deliveries_confirmed', 1);

        return response()->json(['message' => 'Delivery confirmed. Payment event queued.']);
    }

    private function processDispute(
        Request $request,
        ?int $orderId,
        ?int $splitLineId
    ): JsonResponse {
        $validated = $request->validate([
            'reason'     => 'required|in:NOT_RECEIVED,PARTIAL_DELIVERY,WRONG_ITEMS,DAMAGED_GOODS',
            'photo_path' => 'nullable|string|max:500',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $now = Carbon::now('UTC');

        $confirmation = DB::table('delivery_confirmations')
            ->where('order_id', $orderId)
            ->where('split_line_id', $splitLineId)
            ->whereNull('confirmed_at')
            ->first();

        if (! $confirmation) {
            return response()->json(['message' => 'Delivery confirmation not found.'], 404);
        }

        $slaHours = (int) (DB::table('system_settings')
            ->where('key', 'dispute_sla_hours')
            ->value('value') ?? 48);

        DB::table('delivery_disputes')->insert([
            'delivery_confirmation_id' => $confirmation->id,
            'raised_by'                => $request->user()->id,
            'raised_at'                => $now,
            'reason'                   => $validated['reason'],
            'photo_path'               => $validated['photo_path'] ?? null,
            'notes'                    => $validated['notes'] ?? null,
            'status'                   => 'OPEN',
            'sla_deadline_at'          => $now->copy()->addHours($slaHours),
            'created_at'               => $now,
            'updated_at'               => $now,
        ]);

        return response()->json([
            'message'      => 'Dispute raised. Payment held pending resolution.',
            'sla_deadline' => $now->copy()->addHours($slaHours)->toISOString(),
        ], 201);
    }
}
