<?php

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
// Module 15 — Patient Search & Availability Engine
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

// ── Token auth (Sanctum) ───────────────────────────────────────────────
// Public — no auth required to issue a token
Route::post('/v1/auth/token', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'issue']);

// Protected — requires valid token
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/v1/auth/token', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'revoke']);
    Route::get('/v1/auth/me', [\App\Http\Controllers\Api\V1\AuthTokenController::class, 'me']);
});
