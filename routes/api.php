<?php

use App\Http\Controllers\Payments\PaymentCallbackController;
use App\Http\Controllers\Payments\PaymentInstructionController;
use App\Http\Controllers\Payments\RetryPaymentController;
use App\Http\Controllers\Store\BasketController;
use App\Http\Controllers\Store\CustodyChainController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\StoreSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes — Dawa Mtaani
|--------------------------------------------------------------------------
| Routes that do not require authentication.
*/

// -------------------------------------------------------
// Module 15 — Customer Search & Availability Engine
// -------------------------------------------------------
Route::prefix('store')->group(function () {
    Route::get('/search', [StoreSearchController::class, 'search']);
    Route::get('/facilities/{ulid}/stock', [StoreSearchController::class, 'facilityStock']);

    // -------------------------------------------------------
    // Module 16 — Online Order & Fulfilment
    // -------------------------------------------------------
    Route::post('/basket/add', [BasketController::class, 'addItem']);
    Route::delete('/basket/item', [BasketController::class, 'removeItem']);
    Route::get('/basket', [BasketController::class, 'getBasket']);
    Route::post('/basket/reserve', [BasketController::class, 'reserve']);

    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
    Route::post('/orders/mpesa-callback', [OrderController::class, 'mpesaCallback']);
    Route::get('/orders/{ulid}', [OrderController::class, 'getOrderStatus']);
    Route::patch('/orders/{ulid}/collected', [OrderController::class, 'markCollected'])
        ->middleware('auth');

    // -------------------------------------------------------
    // Module 17 — Verified Supply Chain Badge
    // -------------------------------------------------------
    Route::get('/products/{productId}/custody-chain', [CustodyChainController::class, 'show']);
    Route::post('/counterfeit-reports', [CustodyChainController::class, 'report']);
});

// -------------------------------------------------------
// Module 7 — Payment Trigger (B2B)
// -------------------------------------------------------

// Safaricom callbacks — no auth, IP whitelist middleware only
Route::prefix('payments')->group(function () {
    Route::post('/mpesa/callback', [PaymentCallbackController::class, 'copayCallback'])
        ->middleware('mpesa.whitelist');
    Route::post('/mpesa/repayment-callback', [PaymentCallbackController::class, 'repaymentCallback'])
        ->middleware('mpesa.whitelist');
});

// Authenticated payment routes
Route::prefix('payments')->middleware('auth:sanctum')->group(function () {
    // Retry co-pay STK push (pharmacy portal)
    Route::post('/retry/{orderUlid}', [RetryPaymentController::class, 'retry']);

    // Payment instructions (network_admin only)
    Route::get('/instructions', [PaymentInstructionController::class, 'index']);
    Route::patch('/instructions/{id}/manual-process', [PaymentInstructionController::class, 'manualProcess']);

    // Repayment records (facility scoped)
    Route::get('/repayments', [PaymentInstructionController::class, 'repayments']);
});

// ── Token auth (Sanctum) ───────────────────────────────────────────────
// Public — no auth required to issue a token
Route::post('/v1/auth/token', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'issue']);

// Protected — requires valid token
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/v1/auth/token', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'revoke']);
    Route::get('/v1/auth/me', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'me']);
});

// Kenya administrative location lookup (public — used by registration forms)
Route::prefix('kenya')->name('api.kenya.')->group(function () {
    Route::get('/counties', [\App\Http\Controllers\KenyaLocationController::class, 'counties'])->name('counties');
    Route::get('/constituencies/{county}', [\App\Http\Controllers\KenyaLocationController::class, 'constituencies'])->name('constituencies');
    Route::get('/wards/{constituency}', [\App\Http\Controllers\KenyaLocationController::class, 'wards'])->name('wards');
});
