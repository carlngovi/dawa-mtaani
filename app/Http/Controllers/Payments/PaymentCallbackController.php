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
        $payload = $request->all();

        Log::info('MpesaCallback: copay callback received', [
            'checkout_request_id' => data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            'result_code'         => data_get($payload, 'Body.stkCallback.ResultCode'),
        ]);

        $this->repaymentService->handleCopayCallback($payload);

        // Safaricom expects a 200 with this exact structure
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
