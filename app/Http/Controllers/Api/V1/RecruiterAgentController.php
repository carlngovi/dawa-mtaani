<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruiterAgentController extends Controller
{
    // GET /api/v1/admin/recruiter/firms/{firmId}/agents
    public function index(Request $request, int $firmId): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $agents = DB::table('recruiter_agents')
            ->where('firm_id', $firmId)
            ->orderBy('parent_agent_id')
            ->orderBy('agent_name')
            ->get();

        // Build tree structure
        $tree = $this->buildTree($agents->toArray(), null);

        return response()->json([
            'firm_id' => $firmId,
            'agents'  => $tree,
            'total'   => $agents->count(),
        ]);
    }

    // POST /api/v1/admin/recruiter/firms/{firmId}/agents
    public function store(Request $request, int $firmId): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $firm = DB::table('recruiter_firms')->where('id', $firmId)->first();
        if (! $firm) {
            return response()->json(['message' => 'Firm not found.'], 404);
        }

        $validated = $request->validate([
            'agent_name'       => 'required|string|max:255',
            'agent_phone'      => ['required', 'string', 'regex:/^(\+254|07|01)\d{8,9}$/'],
            'agent_role_label' => 'required|string|max:100',
            'parent_agent_id'  => 'nullable|integer|exists:recruiter_agents,id',
        ]);

        // Verify parent agent belongs to same firm
        if ($validated['parent_agent_id'] ?? null) {
            $parentBelongsToFirm = DB::table('recruiter_agents')
                ->where('id', $validated['parent_agent_id'])
                ->where('firm_id', $firmId)
                ->exists();

            if (! $parentBelongsToFirm) {
                return response()->json([
                    'message' => 'Parent agent does not belong to this firm.',
                ], 422);
            }
        }

        $id = DB::table('recruiter_agents')->insertGetId([
            'firm_id'          => $firmId,
            'parent_agent_id'  => $validated['parent_agent_id'] ?? null,
            'agent_name'       => $validated['agent_name'],
            'agent_phone'      => $validated['agent_phone'],
            'agent_role_label' => $validated['agent_role_label'],
            'status'           => 'ACTIVE',
            'created_at'       => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message'  => 'Agent created.',
            'agent_id' => $id,
        ], 201);
    }

    // PATCH /api/v1/admin/recruiter/agents/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'agent_name'       => 'sometimes|string|max:255',
            'agent_phone'      => ['sometimes', 'string', 'regex:/^(\+254|07|01)\d{8,9}$/'],
            'agent_role_label' => 'sometimes|string|max:100',
            'parent_agent_id'  => 'nullable|integer|exists:recruiter_agents,id',
            'status'           => 'sometimes|in:ACTIVE,INACTIVE',
        ]);

        $updated = DB::table('recruiter_agents')
            ->where('id', $id)
            ->update($validated);

        if (! $updated) {
            return response()->json(['message' => 'Agent not found.'], 404);
        }

        return response()->json(['message' => 'Agent updated.']);
    }

    // PATCH /api/v1/admin/recruiter/agents/{id}/deactivate
    public function deactivate(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reassign_children_to' => 'nullable|integer|exists:recruiter_agents,id',
        ]);

        $agent = DB::table('recruiter_agents')->where('id', $id)->first();

        if (! $agent) {
            return response()->json(['message' => 'Agent not found.'], 404);
        }

        DB::transaction(function () use ($agent, $validated, $id) {
            // Reassign children if specified
            if ($validated['reassign_children_to'] ?? null) {
                DB::table('recruiter_agents')
                    ->where('parent_agent_id', $id)
                    ->update(['parent_agent_id' => $validated['reassign_children_to']]);
            }

            // Deactivate agent
            DB::table('recruiter_agents')
                ->where('id', $id)
                ->update(['status' => 'INACTIVE']);
        });

        return response()->json([
            'message'              => 'Agent deactivated.',
            'children_reassigned'  => isset($validated['reassign_children_to']),
        ]);
    }

    // -------------------------------------------------------
    // Helper — build nested tree from flat list
    // -------------------------------------------------------
    private function buildTree(array $agents, ?int $parentId): array
    {
        $branch = [];

        foreach ($agents as $agent) {
            if ($agent->parent_agent_id === $parentId) {
                $children = $this->buildTree($agents, $agent->id);
                $node = (array) $agent;
                $node['children'] = $children;
                $branch[] = $node;
            }
        }

        return $branch;
    }
}
