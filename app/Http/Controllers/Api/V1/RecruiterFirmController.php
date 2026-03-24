<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruiterFirmController extends Controller
{
    // GET /api/v1/admin/recruiter/firms
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $firms = DB::table('recruiter_firms')
            ->orderBy('firm_name')
            ->paginate(20);

        return response()->json($firms);
    }

    // POST /api/v1/admin/recruiter/firms
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'firm_name'             => 'required|string|max:255',
            'commission_rate_kes'   => 'nullable|numeric|min:0',
            'cascade_config'        => 'nullable|array',
            'bank_account_details'  => 'nullable|string|max:500',
        ]);

        $now = Carbon::now('UTC');

        $id = DB::table('recruiter_firms')->insertGetId([
            'firm_name'             => $validated['firm_name'],
            'commission_rate_kes'   => $validated['commission_rate_kes'] ?? 0.00,
            'cascade_config'        => isset($validated['cascade_config'])
                                        ? json_encode($validated['cascade_config'])
                                        : null,
            'bank_account_details'  => $validated['bank_account_details'] ?? null,
            'status'                => 'ACTIVE',
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        return response()->json([
            'message' => 'Recruiter firm created.',
            'id'      => $id,
        ], 201);
    }

    // PATCH /api/v1/admin/recruiter/firms/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'commission_rate_kes'   => 'sometimes|numeric|min:0',
            'cascade_config'        => 'sometimes|nullable|array',
            'bank_account_details'  => 'sometimes|nullable|string|max:500',
            'status'                => 'sometimes|in:ACTIVE,SUSPENDED',
        ]);

        if (isset($validated['cascade_config'])) {
            $validated['cascade_config'] = json_encode($validated['cascade_config']);
        }

        $validated['updated_at'] = Carbon::now('UTC');

        DB::table('recruiter_firms')->where('id', $id)->update($validated);

        return response()->json(['message' => 'Firm updated.']);
    }

    // GET /api/v1/admin/recruiter/firms/{id}/ledger
    public function ledger(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $entries = DB::table('recruiter_ledger_entries as rle')
            ->join('recruiter_agents as ra', 'rle.agent_id', '=', 'ra.id')
            ->where('rle.firm_id', $id)
            ->select([
                'rle.*',
                'ra.agent_name',
                'ra.agent_role_label',
            ])
            ->orderBy('rle.created_at', 'desc')
            ->paginate(50);

        return response()->json($entries);
    }

    // GET /api/v1/admin/recruiter/firms/{id}/activations
    public function activations(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('recruiter_activation_events as rae')
            ->join('recruiter_agents as ra', 'rae.agent_id', '=', 'ra.id')
            ->join('facilities as f', 'rae.facility_id', '=', 'f.id')
            ->where('rae.firm_id', $id)
            ->select([
                'rae.*',
                'ra.agent_name',
                'ra.agent_role_label',
                'f.facility_name',
                'f.county',
            ])
            ->orderBy('rae.created_at', 'desc');

        if ($request->filled('trigger_event')) {
            $query->where('rae.trigger_event', $request->trigger_event);
        }

        if ($request->filled('reconciliation_status')) {
            $query->where('rae.reconciliation_status', $request->reconciliation_status);
        }

        return response()->json($query->paginate(30));
    }

    // PATCH /api/v1/admin/recruiter/activations/{id}/reconcile
    public function reconcile(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reconciliation_status' => 'required|in:RECONCILED,DISPUTED,ADJUSTED',
            'reconciliation_note'   => 'required|string|max:1000',
        ]);

        $updated = DB::table('recruiter_activation_events')
            ->where('id', $id)
            ->update([
                'reconciliation_status' => $validated['reconciliation_status'],
                'reconciliation_note'   => $validated['reconciliation_note'],
                'reconciled_by'         => $request->user()->id,
                'reconciled_at'         => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Activation event not found.'], 404);
        }

        return response()->json(['message' => 'Activation event reconciled.']);
    }
}
