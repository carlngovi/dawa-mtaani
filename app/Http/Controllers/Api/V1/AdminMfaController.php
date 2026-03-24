<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMfaController extends Controller
{
    // POST /api/v1/admin/users/{id}/mfa/disable
    public function disable(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reason'                   => 'required|string|max:500',
            'identity_verified'        => 'required|boolean|accepted',
            'identity_verification_method' => 'required|in:IN_PERSON,VIDEO_CALL',
        ]);

        $user = DB::table('users')->where('id', $id)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $now = Carbon::now('UTC');

        // Disable MFA
        DB::table('users')
            ->where('id', $id)
            ->update([
                'mfa_enabled' => false,
                'updated_at'  => $now,
            ]);

        // Invalidate all existing backup codes
        DB::table('mfa_backup_codes')
            ->where('user_id', $id)
            ->whereNull('used_at')
            ->update(['used_at' => $now]);

        // Log HIGH severity security event
        DB::table('security_events')->insert([
            'user_id'    => $id,
            'event_type' => 'LOGIN_FAILURE',
            'severity'   => 'HIGH',
            'details'    => json_encode([
                'action'                       => 'mfa_disabled_by_admin',
                'disabled_by'                  => $request->user()->id,
                'reason'                       => $validated['reason'],
                'identity_verification_method' => $validated['identity_verification_method'],
            ]),
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'created_at' => $now,
        ]);

        // Write audit log
        DB::table('audit_logs')->insert([
            'user_id'        => $request->user()->id,
            'action'         => 'mfa_disabled_for_user',
            'model_type'     => 'User',
            'model_id'       => $id,
            'payload_after'  => json_encode([
                'mfa_enabled'                  => false,
                'disabled_by'                  => $request->user()->id,
                'reason'                       => $validated['reason'],
                'identity_verification_method' => $validated['identity_verification_method'],
            ]),
            'ip_address'     => $request->ip() ?? '0.0.0.0',
            'created_at'     => $now,
        ]);

        return response()->json([
            'message' => 'MFA disabled. User must re-enable MFA and generate new backup codes on next login.',
        ]);
    }
}
