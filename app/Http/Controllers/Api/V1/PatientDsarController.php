<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PatientDsarController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_phone' => 'required|string|max:20',
            'otp'           => 'required|string',
            'request_type'  => 'required|in:ACCESS,EXPORT,DELETION',
        ]);

        $phone = $validated['patient_phone'];
        $rateLimitKey = 'dsar:' . $phone;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'message' => 'Too many attempts. DSAR requests from this number are blocked for 1 hour.',
            ], 429);
        }

        // Verify OTP via mfa_requests table
        $otpRecord = DB::table('mfa_requests')
            ->where('operation_type', 'DSAR_VERIFICATION')
            ->where('verification_code', $validated['otp'])
            ->where('expires_at', '>', Carbon::now('UTC'))
            ->whereNull('verified_at')
            ->first();

        if (! $otpRecord) {
            RateLimiter::hit($rateLimitKey, 3600);

            return response()->json([
                'message' => 'Invalid or expired OTP. Please request a new code.',
            ], 422);
        }

        // Mark OTP as used
        DB::table('mfa_requests')
            ->where('id', $otpRecord->id)
            ->update(['verified_at' => Carbon::now('UTC')]);

        RateLimiter::clear($rateLimitKey);

        // Hash the phone number — never store raw
        $salt = env('DATA_ANONYMISATION_SALT', '');
        $phoneHash = hash('sha256', $phone . $salt);

        $ulid = Str::ulid();
        $slaDeadline = Carbon::now('UTC')->addDays(30);

        DB::table('patient_dsar_requests')->insert([
            'ulid'               => $ulid,
            'patient_phone_hash' => $phoneHash,
            'request_type'       => $validated['request_type'],
            'status'             => 'PENDING',
            'sla_deadline_at'    => $slaDeadline,
            'created_at'         => Carbon::now('UTC'),
            'updated_at'         => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message'      => 'Your request has been received and will be processed within 30 days.',
            'ulid'         => $ulid,
            'sla_deadline' => $slaDeadline->toISOString(),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $requests = DB::table('patient_dsar_requests')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($requests);
    }

    public function approve(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()?->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $dsarRequest = DB::table('patient_dsar_requests')
            ->where('ulid', $ulid)
            ->where('status', 'PENDING')
            ->first();

        if (! $dsarRequest) {
            return response()->json(['message' => 'Request not found or not pending.'], 404);
        }

        DB::table('patient_dsar_requests')
            ->where('ulid', $ulid)
            ->update([
                'status'      => 'APPROVED',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => Carbon::now('UTC'),
                'updated_at'  => Carbon::now('UTC'),
            ]);

        return response()->json(['message' => 'DSAR request approved. Processing queued.']);
    }
}
