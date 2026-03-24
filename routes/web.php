<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// -------------------------------------------------------
// Public
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

// -------------------------------------------------------
// Admin portal
// -------------------------------------------------------
Route::middleware(['auth', 'role:network_admin|network_field_agent'])
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
        Route::get('/monitoring', [\App\Http\Controllers\Web\AdminMonitoringController::class, 'index']);
        Route::get('/security', [\App\Http\Controllers\Web\AdminSecurityController::class, 'index']);
        Route::get('/audit-log', [\App\Http\Controllers\Web\AdminAuditLogController::class, 'index']);
        Route::get('/recruiter', [\App\Http\Controllers\Web\AdminRecruiterController::class, 'index']);
        Route::get('/dpa', [\App\Http\Controllers\Web\AdminDpaController::class, 'index']);
        Route::get('/reports', [\App\Http\Controllers\Web\AdminReportsController::class, 'index']);
    });

// -------------------------------------------------------
// Retail portal
// -------------------------------------------------------
Route::middleware(['auth', 'role:retail_facility|group_owner'])
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
// Wholesale portal
// -------------------------------------------------------
Route::middleware(['auth', 'role:wholesale_facility'])
    ->prefix('wholesale')
    ->group(function () {
        Route::get('/orders', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'index']);
        Route::get('/price-lists', [\App\Http\Controllers\Web\WholesalePriceListsController::class, 'index']);
        Route::get('/stock', [\App\Http\Controllers\Web\WholesaleStockController::class, 'index']);
        Route::get('/performance', [\App\Http\Controllers\Web\WholesalePerformanceController::class, 'index']);
    });

require __DIR__.'/auth.php';
