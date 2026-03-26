<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Google OAuth
// -------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // Facility (retail_facility) self-registration
    Route::get('/register/facility', [\App\Http\Controllers\Auth\FacilityRegistrationController::class, 'create'])->name('register.facility');
    Route::post('/register/facility', [\App\Http\Controllers\Auth\FacilityRegistrationController::class, 'store'])->name('register.facility.store');
    Route::get('/register/facility/pending', [\App\Http\Controllers\Auth\FacilityRegistrationController::class, 'pending'])->name('register.facility.pending');

    // Google OAuth for facility registration
    Route::get('/auth/google/facility', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'facilityRedirect'])->name('auth.google.facility');
    Route::get('/auth/google/facility/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'facilityCallback'])->name('auth.google.facility.callback');

    // Patient registration
    Route::get('/register/patient', [\App\Http\Controllers\Auth\PatientRegistrationController::class, 'create'])->name('register.patient');
    Route::post('/register/patient', [\App\Http\Controllers\Auth\PatientRegistrationController::class, 'store'])->name('register.patient.store');

    // Google OAuth for patient registration
    Route::get('/auth/google/patient', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'patientRedirect'])->name('auth.google.patient');
    Route::get('/auth/google/patient/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'patientCallback'])->name('auth.google.patient.callback');
});

// -------------------------------------------------------
// Public invitation accept routes (no auth needed)
// -------------------------------------------------------
Route::get('/register/accept/{token}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'showAccept'])->name('invitation.accept');
Route::post('/register/accept/{token}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'acceptStore'])->name('invitation.accept.store');

// -------------------------------------------------------
// Root redirect — uses RoleRedirectController for 14-role RBAC
// -------------------------------------------------------
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect('/login');
    }
    return App\Http\Controllers\Web\RoleRedirectController::redirectForUser(Auth::user());
});

Route::get('/dashboard', function () {
    return App\Http\Controllers\Web\RoleRedirectController::redirectForUser(Auth::user());
})->middleware('auth')->name('dashboard');

// -------------------------------------------------------
// Admin portal — auth only, role checked in controller
// -------------------------------------------------------
Route::middleware(['auth', 'portal', 'financial.timeout'])
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
        Route::get('/credit', [\App\Http\Controllers\Web\AdminCreditController::class, 'index'])->name('admin.credit.index');
        Route::post('/credit/tranches', [\App\Http\Controllers\Web\AdminCreditController::class, 'storeTranche'])->name('admin.credit.tranches.store');
        Route::patch('/credit/tranches/{tranche}/toggle', [\App\Http\Controllers\Web\AdminCreditController::class, 'toggleTranche'])->name('admin.credit.tranches.toggle');
        Route::post('/credit/tranches/{tranche}/parties', [\App\Http\Controllers\Web\AdminCreditController::class, 'storeParty'])->name('admin.credit.parties.store');
        Route::post('/credit/tranches/{tranche}/tiers', [\App\Http\Controllers\Web\AdminCreditController::class, 'storeTier'])->name('admin.credit.tiers.store');
        Route::post('/credit/progression', [\App\Http\Controllers\Web\AdminCreditController::class, 'storeProgressionRule'])->name('admin.credit.progression.store');
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
        Route::get('/registrations', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'index']);
        Route::get('/registrations/{ulid}', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'show']);
        Route::post('/registrations/{ulid}/approve', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'approve']);
        Route::post('/registrations/{ulid}/activate', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'activate']);
        Route::post('/registrations/{ulid}/reject', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'reject']);
        Route::post('/registrations/{ulid}/verify-ppb', [\App\Http\Controllers\Web\AdminRegistrationsController::class, 'verifyPpb']);
        Route::get('/placers', [\App\Http\Controllers\Web\AdminPlacersController::class, 'index']);
        Route::get('/invitations', [\App\Http\Controllers\Web\AdminInvitationController::class, 'index']);
        Route::post('/invitations', [\App\Http\Controllers\Web\AdminInvitationController::class, 'store']);
        Route::delete('/invitations/{id}', [\App\Http\Controllers\Web\AdminInvitationController::class, 'destroy']);
    });

// -------------------------------------------------------
// Retail portal — auth only
// -------------------------------------------------------
Route::middleware(['auth', 'portal'])
    ->prefix('retail')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Web\RetailDashboardController::class, 'index']);
        Route::get('/catalogue', [\App\Http\Controllers\Web\RetailCatalogueController::class, 'index']);
        Route::get('/cart', [\App\Http\Controllers\Web\RetailCatalogueController::class, 'cart']);
        Route::get('/basket', [\App\Http\Controllers\Web\RetailOrdersController::class, 'basket']);
        Route::get('/orders', [\App\Http\Controllers\Web\RetailOrdersController::class, 'index']);
        Route::post('/orders', [\App\Http\Controllers\Web\RetailOrdersController::class, 'store']);
        Route::get('/orders/{ulid}', [\App\Http\Controllers\Web\RetailOrdersController::class, 'show']);
        Route::get('/orders/{ulid}/payment-pending', [\App\Http\Controllers\Web\RetailOrdersController::class, 'paymentPending']);
        Route::get('/orders/{ulid}/payment-status', [\App\Http\Controllers\Web\RetailOrdersController::class, 'paymentStatus']);
        Route::post('/orders/{ulid}/dispute', [\App\Http\Controllers\Web\RetailOrdersController::class, 'raiseDispute']);
        Route::get('/favourites', [\App\Http\Controllers\Web\RetailFavouritesController::class, 'index']);
        Route::get('/credit', [\App\Http\Controllers\Web\RetailCreditController::class, 'index'])->name('retail.credit.index');
        Route::get('/lpo', [\App\Http\Controllers\Web\RetailLpoController::class, 'index']);
        Route::get('/quality-flags', [\App\Http\Controllers\Web\RetailQualityFlagsController::class, 'index']);
        Route::get('/pos', [\App\Http\Controllers\Web\RetailPosController::class, 'index']);
        Route::get('/stock', [\App\Http\Controllers\Web\RetailStockController::class, 'index']);
    });

// -------------------------------------------------------
// Wholesale portal — auth only
// -------------------------------------------------------
Route::middleware(['auth', 'portal', 'financial.timeout'])
    ->prefix('wholesale')
    ->group(function () {
        Route::get('/orders', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'index']);
        Route::get('/orders/{ulid}', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'show']);
        Route::post('/orders/{ulid}/confirm', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'confirm']);
        Route::post('/orders/{ulid}/pack', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'pack']);
        Route::post('/orders/{ulid}/dispatch', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'dispatch']);
        Route::post('/orders/bulk-dispatch', [\App\Http\Controllers\Web\WholesaleOrdersController::class, 'bulkDispatch']);
        Route::get('/price-lists', [\App\Http\Controllers\Web\WholesalePriceListsController::class, 'index']);
        Route::get('/stock', [\App\Http\Controllers\Web\WholesaleStockController::class, 'index']);
        Route::get('/performance', [\App\Http\Controllers\Web\WholesalePerformanceController::class, 'index']);
        Route::get('/dispatch', [\App\Http\Controllers\Web\WholesaleDispatchController::class, 'index']);
        Route::get('/disputes', [\App\Http\Controllers\Web\WholesaleDisputesController::class, 'index']);
        Route::get('/settlement', [\App\Http\Controllers\Web\WholesaleSettlementController::class, 'index']);
    });

// ── Network field agent portal ────────────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('field')->group(function () {
    Route::get('/pharmacies', [\App\Http\Controllers\Web\FieldPharmaciesController::class, 'index']);
    Route::get('/register', [\App\Http\Controllers\Web\FieldRegisterController::class, 'index']);
    Route::get('/gps/{ulid?}', [\App\Http\Controllers\Web\FieldGpsController::class, 'index']);
    Route::get('/mystery-shop', [\App\Http\Controllers\Web\FieldMysteryShopController::class, 'index']);
    Route::get('/disputes', [\App\Http\Controllers\Web\FieldDisputesController::class, 'index']);
});

// ── Sales rep portal ──────────────────────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('rep')->group(function () {
    Route::get('/pharmacies', [\App\Http\Controllers\Web\RepPharmaciesController::class, 'index']);
    Route::get('/pharmacies/{ulid}', [\App\Http\Controllers\Web\RepPharmaciesController::class, 'show']);
    Route::get('/summary', [\App\Http\Controllers\Web\RepSummaryController::class, 'index']);
});

// ── Logistics facility portal (SGA) ───────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('logistics')->group(function () {
    Route::get('/deliveries', [\App\Http\Controllers\Web\LogisticsDeliveriesController::class, 'index']);
    Route::get('/routes', [\App\Http\Controllers\Web\LogisticsRoutesController::class, 'index']);
    Route::get('/disputes', [\App\Http\Controllers\Web\LogisticsDisputesController::class, 'index']);
    Route::get('/invoices', [\App\Http\Controllers\Web\LogisticsInvoicesController::class, 'index']);
});

// ── Group owner portal ────────────────────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('group')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\GroupDashboardController::class, 'index']);
    Route::get('/order', [\App\Http\Controllers\Web\GroupOrderController::class, 'index']);
    Route::get('/credit', [\App\Http\Controllers\Web\GroupCreditController::class, 'index']);
    Route::get('/placers', [\App\Http\Controllers\Web\GroupPlacersController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\Web\GroupOrderHistoryController::class, 'index']);
});

// ── Shared accountant portal ──────────────────────────────────────────────
Route::middleware(['auth', 'portal', 'financial.timeout'])->prefix('finance')->group(function () {
    Route::get('/settlement', [\App\Http\Controllers\Web\FinanceSettlementController::class, 'index']);
    Route::get('/credit', [\App\Http\Controllers\Web\FinanceCreditController::class, 'index']);
    Route::get('/sweeps', [\App\Http\Controllers\Web\FinanceSweepsController::class, 'index']);
    Route::get('/payroll', [\App\Http\Controllers\Web\FinancePayrollController::class, 'index']);
});

// ── Admin support portal (Tier 4 read-only) ───────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('support')->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\Web\SupportTicketsController::class, 'index']);
    Route::get('/facilities', [\App\Http\Controllers\Web\SupportFacilitiesController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\Web\SupportOrdersController::class, 'index']);
});

// ── Assistant admin portal (Tier 3) ──────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('assistant')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\AssistantDashboardController::class, 'index']);
});

// ── Super admin portal (Tier 1) ───────────────────────────────────────────
Route::middleware(['auth', 'portal', 'financial.timeout'])->prefix('super')->group(function () {
    Route::get('/settings', [\App\Http\Controllers\Web\SuperSettingsController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\Web\SuperSettingsController::class, 'update']);
    Route::get('/roles', [\App\Http\Controllers\Web\SuperRolesController::class, 'index']);
    Route::get('/fees', [\App\Http\Controllers\Web\SuperFeesController::class, 'index']);
    Route::get('/t0-approvals', [\App\Http\Controllers\Web\SuperT0ApprovalsController::class, 'index']);
    Route::post('/t0-approvals/{id}/confirm', [\App\Http\Controllers\Web\SuperT0ApprovalsController::class, 'confirm']);
    Route::post('/t0-approvals/{id}/reject', [\App\Http\Controllers\Web\SuperT0ApprovalsController::class, 'reject']);
    Route::get('/design-fee', [\App\Http\Controllers\Web\SuperDesignFeeController::class, 'index']);
    Route::post('/design-fee/{tranche}/release', [\App\Http\Controllers\Web\SuperDesignFeeController::class, 'release']);
});

// ── Patient portal (B2C store) ────────────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('store')->group(function () {
    Route::get('/', [\App\Http\Controllers\Web\StoreBrowseController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\Web\StoreOrdersController::class, 'index']);
    Route::get('/orders/{ulid}', [\App\Http\Controllers\Web\StoreOrdersController::class, 'show']);
    Route::get('/report/counterfeit', [\App\Http\Controllers\Web\StoreCounterfeitController::class, 'index']);
    Route::post('/report/counterfeit', [\App\Http\Controllers\Web\StoreCounterfeitController::class, 'store']);
    Route::get('/basket', [\App\Http\Controllers\Web\StoreBrowseController::class, 'basket'])->name('store.basket');
    Route::post('/basket/checkout', [\App\Http\Controllers\Web\StoreBrowseController::class, 'patientCheckout'])->name('store.basket.checkout');
    Route::get('/orders/{ulid}/pending', [\App\Http\Controllers\Web\StoreBrowseController::class, 'paymentPending'])->name('store.payment-pending');
    Route::get('/orders/{ulid}/payment-status', [\App\Http\Controllers\Web\StoreBrowseController::class, 'orderStatus'])->name('store.order.status');
    Route::post('/orders/{ulid}/check-payment', [\App\Http\Controllers\Web\StoreBrowseController::class, 'checkPayment'])->name('store.check-payment');
    Route::get('/{facilityUlid}', [\App\Http\Controllers\Web\StoreBrowseController::class, 'storefront'])->name('store.storefront');
    Route::get('/{facilityUlid}/checkout', [\App\Http\Controllers\Web\StoreBrowseController::class, 'checkout'])->name('store.checkout');
});

// ── Technical admin portal (Tier 0) ───────────────────────────────────────
Route::middleware(['auth', 'portal'])->prefix('tech')->group(function () {
    Route::get('/diagnostics', [\App\Http\Controllers\Web\TechDiagnosticsController::class, 'index']);
    Route::post('/query', [\App\Http\Controllers\Web\TechDiagnosticsController::class, 'query']);
    Route::get('/jobs', [\App\Http\Controllers\Web\TechJobsController::class, 'index']);
    Route::get('/write', [\App\Http\Controllers\Web\TechWriteController::class, 'index']);
    Route::get('/incidents', [\App\Http\Controllers\Web\TechIncidentsController::class, 'index']);
});

require __DIR__.'/auth.php';
