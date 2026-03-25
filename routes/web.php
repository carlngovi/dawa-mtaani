<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Google OAuth
// -------------------------------------------------------
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// -------------------------------------------------------
// Public invitation accept routes (no auth needed)
// -------------------------------------------------------
Route::get('/register/accept/{token}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'showAccept'])->name('invitation.accept');
Route::post('/register/accept/{token}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'acceptStore'])->name('invitation.accept.store');

// -------------------------------------------------------
// Block public registration — invitation only
// -------------------------------------------------------
Route::get('register', function () {
    return redirect('/login')->with('status', 'Registration is by invitation only. Contact your network administrator.');
})->name('register');
Route::post('register', function () {
    return redirect('/login')->with('status', 'Registration is by invitation only.');
});

// -------------------------------------------------------
// Root redirect
// -------------------------------------------------------
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('network_admin') || $user->hasRole('network_field_agent')) {
            return redirect('/admin/dashboard');
        }
        if ($user->hasRole('wholesale_facility')) {
            return redirect('/wholesale/orders');
        }
        if ($user->hasRole('retail_facility') || $user->hasRole('group_owner')) {
            return redirect('/retail/dashboard');
        }
    }
    return redirect('/login');
});

// Named dashboard route for Breeze compatibility
Route::get('/dashboard', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('network_admin') || $user->hasRole('network_field_agent')) {
            return redirect('/admin/dashboard');
        }
        if ($user->hasRole('wholesale_facility')) {
            return redirect('/wholesale/orders');
        }
    }
    return redirect('/retail/dashboard');
})->middleware('auth')->name('dashboard');

// -------------------------------------------------------
// Admin portal — auth only, role checked in controller
// -------------------------------------------------------
Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Web\AdminDashboardController::class, 'index']);
        Route::get('/facilities', [\App\Http\Controllers\Web\AdminFacilitiesController::class, 'index']);
        Route::get('/facilities/{ulid}', [\App\Http\Controllers\Web\AdminFacilitiesController::class, 'show']);
        Route::get('/orders', [\App\Http\Controllers\Web\AdminOrdersController::class, 'index']);
        Route::get('/flags', [\App\Http\Controllers\Web\AdminFlagsController::class, 'index']);
        Route::get('/disputes', [\App\Http\Controllers\Web\AdminDisputesController::class, 'index']);
        Route::get('/quality-flags', [\App\Http\Controllers\Web\AdminQualityFlagsController::class, 'index']);
        Route::get('/ppb-registry', [\App\Http\Controllers\Web\AdminPpbRegistryController::class, 'index']);
        Route::post('/ppb-registry/upload', [\App\Http\Controllers\Web\AdminPpbRegistryController::class, 'upload']);
        Route::get('/monitoring', [\App\Http\Controllers\Web\AdminMonitoringController::class, 'index']);
        Route::get('/security', [\App\Http\Controllers\Web\AdminSecurityController::class, 'index']);
        Route::get('/audit-log', [\App\Http\Controllers\Web\AdminAuditLogController::class, 'index']);
        Route::get('/recruiter', [\App\Http\Controllers\Web\AdminRecruiterController::class, 'index']);
        Route::get('/dpa', [\App\Http\Controllers\Web\AdminDpaController::class, 'index']);
        Route::get('/reports', [\App\Http\Controllers\Web\AdminReportsController::class, 'index']);
        Route::get('/credit', [\App\Http\Controllers\Web\AdminCreditController::class, 'index']);
        Route::get('/products', [\App\Http\Controllers\Web\AdminProductsController::class, 'index']);
        Route::get('/categories', [\App\Http\Controllers\Web\AdminCategoriesController::class, 'index']);
        Route::get('/wallets', [\App\Http\Controllers\Web\AdminWalletsController::class, 'index']);
        Route::get('/bank-accounts', [\App\Http\Controllers\Web\AdminBankAccountsController::class, 'index']);
        Route::get('/payments', [\App\Http\Controllers\Web\AdminPaymentsController::class, 'index']);
        Route::get('/inventory', [\App\Http\Controllers\Web\AdminInventoryController::class, 'index']);
        Route::get('/sales', [\App\Http\Controllers\Web\AdminSalesController::class, 'index']);
        Route::get('/customers', [\App\Http\Controllers\Web\AdminCustomersController::class, 'index']);
        Route::get('/notifications', [\App\Http\Controllers\Web\AdminNotificationsController::class, 'index']);
        Route::get('/settings', [\App\Http\Controllers\Web\AdminSettingsController::class, 'index']);
        Route::get('/invitations', [\App\Http\Controllers\Web\AdminInvitationController::class, 'index']);
        Route::post('/invitations', [\App\Http\Controllers\Web\AdminInvitationController::class, 'store']);
        Route::delete('/invitations/{id}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'destroy']);
    });

// -------------------------------------------------------
// Retail portal — auth only
// -------------------------------------------------------
Route::middleware(['auth'])
    ->prefix('retail')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Web\RetailDashboardController::class, 'index']);
        Route::get('/catalogue', [\App\Http\Controllers\Web\RetailCatalogueController::class, 'index']);
        Route::get('/orders', [\App\Http\Controllers\Web\RetailOrdersController::class, 'index']);
        Route::get('/orders/{ulid}', [\App\Http\Controllers\Web\RetailOrdersController::class, 'show']);
        Route::get('/favourites', [\App\Http\Controllers\Web\RetailFavouritesController::class, 'index']);
        Route::get('/credit', [\App\Http\Controllers\Web\RetailCreditController::class, 'index']);
        Route::get('/lpo', [\App\Http\Controllers\Web\RetailLpoController::class, 'index']);
        Route::get('/quality-flags', [\App\Http\Controllers\Web\RetailQualityFlagsController::class, 'index']);
        Route::get('/pos', [\App\Http\Controllers\Web\RetailPosController::class, 'index']);
        Route::get('/stock', [\App\Http\Controllers\Web\RetailStockController::class, 'index']);
    });

// -------------------------------------------------------
// Wholesale portal — auth only
// -------------------------------------------------------
Route::middleware(['auth'])
    ->prefix('wholesale')
    ->group(function () {
        Route::get('/orders', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'index']);
        Route::get('/price-lists', [\App\Http\Controllers\Web\WholesalePriceListsController::class, 'index']);
        Route::get('/stock', [\App\Http\Controllers\Web\WholesaleStockController::class, 'index']);
        Route::get('/performance', [\App\Http\Controllers\Web\WholesalePerformanceController::class, 'index']);
    });

require __DIR__.'/auth.php';
