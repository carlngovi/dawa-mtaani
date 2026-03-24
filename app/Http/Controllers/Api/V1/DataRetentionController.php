<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataRetentionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $policies = DB::table('data_retention_policies')
            ->orderBy('data_category')
            ->get();

        return response()->json(['policies' => $policies]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'retention_years'  => 'sometimes|integer|min:1|max:50',
            'action_on_expiry' => 'sometimes|in:ANONYMISE,DELETE',
            'is_active'        => 'sometimes|boolean',
        ]);

        $validated['updated_by'] = $request->user()->id;

        DB::table('data_retention_policies')
            ->where('id', $id)
            ->update($validated);

        return response()->json(['message' => 'Policy updated.']);
    }

    public function anonymisationLog(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $log = DB::table('anonymisation_log')
            ->orderBy('started_at', 'desc')
            ->paginate(50);

        return response()->json($log);
    }
}
