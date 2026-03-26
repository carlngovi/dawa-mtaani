<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentInstructionController extends Controller
{
    // GET /api/payments/instructions (network_admin only)
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\PaymentInstruction::class);

        $instructions = DB::table('payment_instructions')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($instructions);
    }

    // PATCH /api/payments/instructions/{id}/manual-process (network_admin only)
    public function manualProcess(Request $request, int $id): JsonResponse
    {
        $this->authorize('manualProcess', \App\Models\PaymentInstruction::class);

        $updated = DB::table('payment_instructions')
            ->where('id', $id)
            ->where('status', 'MANUAL_REVIEW')
            ->update([
                'status'       => 'PROCESSED',
                'processed_at' => now(),
                'party_reference' => $request->input('party_reference'),
                'updated_at'   => now(),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Instruction not found or not in MANUAL_REVIEW status.'], 404);
        }

        return response()->json(['message' => 'Instruction marked as processed.']);
    }

    // GET /api/payments/repayments (facility scoped)
    public function repayments(Request $request): JsonResponse
    {
        $facilityId = $request->user()->facility_id;

        $repayments = DB::table('repayment_records')
            ->where('facility_id', $facilityId)
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($repayments);
    }
}
