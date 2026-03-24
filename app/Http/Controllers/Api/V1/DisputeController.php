<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    // GET /api/v1/disputes
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->leftJoin('orders as o', 'dc.order_id', '=', 'o.id')
            ->select([
                'dd.*',
                'o.ulid as order_ulid',
                'dc.delivered_at',
                'dc.pod_photo_path',
            ])
            ->orderBy('dd.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('dd.status', $request->status);
        }

        if ($request->filled('sla_breached')) {
            $query->where('dd.sla_breached', (bool) $request->sla_breached);
        }

        $disputes = $query->paginate(20);

        return response()->json($disputes);
    }

    // PATCH /api/v1/disputes/{id}/resolve
    public function resolve(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'resolution'      => 'required|in:PAYMENT_CONFIRMED,ORDER_CANCELLED,PARTIAL_PAYMENT',
            'resolved_amount' => 'required_if:resolution,PARTIAL_PAYMENT|nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        $now = Carbon::now('UTC');

        $dispute = DB::table('delivery_disputes')
            ->where('id', $id)
            ->where('status', '!=', 'RESOLVED')
            ->first();

        if (! $dispute) {
            return response()->json(['message' => 'Dispute not found or already resolved.'], 404);
        }

        DB::transaction(function () use ($dispute, $validated, $request, $now) {
            // Update dispute
            DB::table('delivery_disputes')
                ->where('id', $dispute->id)
                ->update([
                    'status'          => 'RESOLVED',
                    'resolved_by'     => $request->user()->id,
                    'resolved_at'     => $now,
                    'resolution'      => $validated['resolution'],
                    'resolved_amount' => $validated['resolved_amount'] ?? null,
                    'updated_at'      => $now,
                ]);

            // Update delivery confirmation
            DB::table('delivery_confirmations')
                ->where('id', $dispute->delivery_confirmation_id)
                ->update([
                    'confirmed_at'      => $now,
                    'confirmed_by'      => $request->user()->id,
                    'confirmation_type' => 'DISPUTED_RESOLVED',
                    'updated_at'        => $now,
                ]);

            // Log to audit trail
            DB::table('audit_logs')->insert([
                'user_id'        => $request->user()->id,
                'action'         => 'dispute_resolved',
                'model_type'     => 'DeliveryDispute',
                'model_id'       => $dispute->id,
                'payload_after'  => json_encode([
                    'resolution'      => $validated['resolution'],
                    'resolved_amount' => $validated['resolved_amount'] ?? null,
                ]),
                'ip_address'     => $request->ip() ?? '0.0.0.0',
                'created_at'     => $now,
            ]);
        });

        return response()->json([
            'message'    => 'Dispute resolved.',
            'resolution' => $validated['resolution'],
        ]);
    }
}
