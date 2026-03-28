<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyPpbOfCounterfeitJob;
use App\Models\CustomerCounterfeitReport;
use App\Models\User;
use App\Notifications\CounterfeitReportedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CustodyChainController extends Controller
{
    public function show(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
        ]);

        $facilityId = (int) $validated['facility_id'];

        $facility = DB::table('facilities')->where('id', $facilityId)->first();

        // OFF_NETWORK → immediately return false
        if ($facility->network_membership !== 'NETWORK') {
            return response()->json([
                'status' => 'success',
                'data'   => ['verified' => false],
                'message' => '',
            ]);
        }

        // EVENT 1 — Supplier identity
        $supplier = DB::table('wholesale_price_lists as wpl')
            ->join('facilities as f', 'f.id', '=', 'wpl.wholesale_facility_id')
            ->where('wpl.product_id', $productId)
            ->where('wpl.is_active', true)
            ->select('f.facility_name as supplier_name', 'f.ppb_licence_number as supplier_ppb_licence')
            ->first();

        if (! $supplier) {
            return response()->json([
                'status' => 'success',
                'data'   => ['verified' => false],
                'message' => '',
            ]);
        }

        // EVENT 2 — Delivery confirmation
        $delivery = DB::table('delivery_confirmations as dc')
            ->join('order_lines as ol', 'ol.order_id', '=', 'dc.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'dc.confirmed_by')
            ->where('ol.product_id', $productId)
            ->where('ol.delivery_facility_id', $facilityId)
            ->whereNotNull('dc.confirmed_at')
            ->select('dc.confirmed_at', 'u.name as confirmed_by_name')
            ->orderByDesc('dc.confirmed_at')
            ->first();

        if (! $delivery) {
            return response()->json([
                'status' => 'success',
                'data'   => ['verified' => false],
                'message' => '',
            ]);
        }

        // EVENT 3 — Order line delivered
        $orderLine = DB::table('order_lines as ol')
            ->join('orders as o', 'o.id', '=', 'ol.order_id')
            ->where('ol.product_id', $productId)
            ->where('ol.delivery_facility_id', $facilityId)
            ->where('o.status', 'DELIVERED')
            ->select('o.confirmed_at as delivered_at', 'o.ulid as order_reference')
            ->orderByDesc('o.confirmed_at')
            ->first();

        if (! $orderLine) {
            return response()->json([
                'status' => 'success',
                'data'   => ['verified' => false],
                'message' => '',
            ]);
        }

        // EVENT 4 — Dispensing record
        $dispensing = DB::table('dispensing_entries')
            ->where('product_id', $productId)
            ->where('facility_id', $facilityId)
            ->orderByDesc('dispensed_at')
            ->select('dispensed_at as last_dispensed_at')
            ->first();

        if (! $dispensing) {
            return response()->json([
                'status' => 'success',
                'data'   => ['verified' => false],
                'message' => '',
            ]);
        }

        // ALL four events present → verified
        return response()->json([
            'status' => 'success',
            'data'   => [
                'verified'            => true,
                'facility_ppb_licence' => $facility->ppb_licence_number,
                'facility_ppb_status'  => $facility->ppb_licence_status,
                'chain'               => [
                    [
                        'event'     => 'sourced',
                        'label'     => 'Sourced from supplier',
                        'actor'     => $supplier->supplier_name,
                        'licence'   => $supplier->supplier_ppb_licence,
                        'timestamp' => $delivery->confirmed_at,
                    ],
                    [
                        'event'     => 'delivered',
                        'label'     => 'Delivery confirmed',
                        'actor'     => $delivery->confirmed_by_name,
                        'timestamp' => $delivery->confirmed_at,
                    ],
                    [
                        'event'          => 'received',
                        'label'          => 'Received by pharmacy',
                        'order_reference' => $orderLine->order_reference,
                        'timestamp'      => $orderLine->delivered_at,
                    ],
                    [
                        'event'     => 'dispensed',
                        'label'     => 'Dispensing recorded',
                        'timestamp' => $dispensing->last_dispensed_at,
                    ],
                ],
            ],
            'message' => '',
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'  => 'required|exists:facilities,id',
            'product_id'   => 'required|exists:products,id',
            'customer_phone' => 'required|string',
            'report_notes' => 'nullable|string|max:1000',
        ]);

        $report = CustomerCounterfeitReport::create($validated);

        // Notify all network_admin users
        $roleExists = \Spatie\Permission\Models\Role::where('name', 'network_admin')
            ->where('guard_name', 'web')
            ->exists();
        $admins = $roleExists ? User::role('network_admin')->get() : collect();

        $report->load(['facility', 'product']);

        Notification::send($admins, new CounterfeitReportedNotification($report));

        // Queue PPB notification
        NotifyPpbOfCounterfeitJob::dispatch($report)->onQueue('quality-flags');

        return response()->json([
            'status'  => 'success',
            'data'    => [],
            'message' => 'Report submitted. Thank you for helping keep the supply chain safe.',
        ]);
    }
}
