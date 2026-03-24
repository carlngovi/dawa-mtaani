<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MfaBackupCodeService;
use App\Services\MfaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MfaController extends Controller
{
    public function __construct(
        private readonly MfaService $mfaService,
        private readonly MfaBackupCodeService $backupCodeService,
    ) {}

    // POST /api/v1/mfa/request
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation_type' => 'required|in:CREDIT_DRAW,PAYMENT_APPROVAL,PRICE_LIST_CHANGE,ROLE_CHANGE,FACILITY_STATUS_CHANGE,DSAR_VERIFICATION',
            'method'         => 'nullable|in:OTP_SMS,OTP_WHATSAPP',
        ]);

        $result = $this->mfaService->request(
            userId: $request->user()->id,
            operationType: $validated['operation_type'],
            method: $validated['method'] ?? 'OTP_SMS'
        );

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 429);
        }

        return response()->json([
            'message' => $result['message'],
            'token'   => $result['token'],
        ]);
    }

    // POST /api/v1/mfa/verify
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation_type' => 'required|in:CREDIT_DRAW,PAYMENT_APPROVAL,PRICE_LIST_CHANGE,ROLE_CHANGE,FACILITY_STATUS_CHANGE,DSAR_VERIFICATION',
            'code'           => 'required|string',
        ]);

        $verified = $this->mfaService->verify(
            userId: $request->user()->id,
            operationType: $validated['operation_type'],
            code: $validated['code'],
            ip: $request->ip() ?? '0.0.0.0'
        );

        if (! $verified) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
                'verified' => false,
            ], 422);
        }

        return response()->json([
            'message'  => 'OTP verified.',
            'verified' => true,
        ]);
    }

    // POST /api/v1/mfa/verify-backup
    public function verifyBackupCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:8',
        ]);

        $verified = $this->backupCodeService->verify(
            userId: $request->user()->id,
            code: strtoupper($validated['code'])
        );

        if (! $verified) {
            return response()->json([
                'message'  => 'Invalid backup code.',
                'verified' => false,
            ], 422);
        }

        $remaining = $this->backupCodeService->getRemainingCount($request->user()->id);

        return response()->json([
            'message'           => 'Backup code verified.',
            'verified'          => true,
            'remaining_codes'   => $remaining,
            'warn_low_codes'    => $remaining < 3,
        ]);
    }

    // POST /api/v1/mfa/backup-codes/generate
    public function generateBackupCodes(Request $request): JsonResponse
    {
        $codes = $this->backupCodeService->generate($request->user()->id);

        return response()->json([
            'message' => 'Backup codes generated. Store these securely — they will not be shown again.',
            'codes'   => $codes,
            'count'   => count($codes),
        ]);
    }

    // GET /api/v1/mfa/backup-codes/count
    public function backupCodeCount(Request $request): JsonResponse
    {
        $remaining = $this->backupCodeService->getRemainingCount($request->user()->id);

        return response()->json([
            'remaining' => $remaining,
            'warn'      => $remaining < 3,
        ]);
    }
}
