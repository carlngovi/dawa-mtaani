<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Integrations\MpesaRepaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function __construct(
        private readonly MpesaRepaymentService $repaymentService,
    ) {}

    // POST /api/payments/mpesa/callback
    // Safaricom STK push callback for B2B co-pay
    public function copayCallback(Request $request): Response
    {
        $payload  = $request->all();
        $callback = $payload['Body']['stkCallback'] ?? [];

        $resultCode   = $callback['ResultCode'] ?? null;
        $resultDesc   = $callback['ResultDesc'] ?? 'No description';
        $checkoutId   = $callback['CheckoutRequestID'] ?? null;

        // Extract metadata items if payment succeeded
        $meta    = collect($callback['CallbackMetadata']['Item'] ?? []);
        $amount  = $meta->firstWhere('Name', 'Amount')['Value'] ?? null;
        $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
        $phone   = $meta->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;

        $outcome = match ((int) $resultCode) {
            0    => "✅ PAYMENT SUCCESSFUL — Receipt: {$receipt}, Amount: KES {$amount}, Phone: {$phone}",
            1    => "❌ INSUFFICIENT FUNDS — Customer does not have enough balance.",
            17   => "❌ M-PESA LIMIT REACHED — Customer has exceeded their M-Pesa transaction limit.",
            1032 => "❌ CANCELLED BY USER — Customer dismissed the STK prompt.",
            1037 => "❌ TIMEOUT — Customer did not respond to the STK prompt.",
            2001 => "❌ WRONG PIN — Customer entered incorrect M-Pesa PIN.",
            default => "❌ FAILED — Code: {$resultCode}, Reason: {$resultDesc}",
        };

        Log::info("MpesaCallback | {$checkoutId} | {$outcome}");

        $this->repaymentService->handleCopayCallback($payload);

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }

    // POST /api/payments/mpesa/repayment-callback
    // Safaricom STK push callback for standard repayment collection
    public function repaymentCallback(Request $request): Response
    {
        $payload = $request->all();

        Log::info('MpesaCallback: repayment callback received', [
            'checkout_request_id' => data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            'result_code'         => data_get($payload, 'Body.stkCallback.ResultCode'),
        ]);

        $this->repaymentService->handleRepaymentCallback($payload);

        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }
}
