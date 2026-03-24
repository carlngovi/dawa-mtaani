<?php

use App\Http\Controllers\Api\V1\JobHealthController;
use App\Http\Controllers\Api\V1\MonitoringDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes — Dawa Mtaani
|--------------------------------------------------------------------------
| All API routes are versioned under /api/v1/
| Routes are added here module by module as each is built.
| Do not add any routes yet — this file is the scaffold only.
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'timestamp' => now()->toISOString(),
    ]);
});

// Module 21 — Integration Health (network_admin only)
Route::get('/admin/integrations/health', [
    \App\Http\Controllers\Api\V1\IntegrationHealthController::class,
    'index',
])->middleware(['auth:sanctum', 'role:network_admin']);

// -------------------------------------------------------
// Module 18 — Offline Sync
// -------------------------------------------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/sync/push', [\App\Http\Controllers\Api\V1\SyncController::class, 'push']);
    Route::get('/sync/pull', [\App\Http\Controllers\Api\V1\SyncController::class, 'pull']);
});

// -------------------------------------------------------
// Module 18 — SMS Fallback (Africa's Talking webhook)
// No auth — incoming from Africa's Talking servers
// -------------------------------------------------------
Route::post('/sms/incoming', [\App\Http\Controllers\Api\V1\SyncController::class, 'smsIncoming']);

// -------------------------------------------------------
// Module 28 — Monitoring & Observability
// -------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/monitoring/summary', [MonitoringDashboardController::class, 'summary']);
    Route::get('/admin/monitoring/slo', [MonitoringDashboardController::class, 'sloCompliance']);
    Route::patch('/admin/monitoring/alerts/{id}/acknowledge', [MonitoringDashboardController::class, 'acknowledgeAlert']);
    Route::get('/admin/jobs/health', [JobHealthController::class, 'index']);
});

// -------------------------------------------------------
// Module 29 — Data Retention & Kenya DPA Compliance
// -------------------------------------------------------

// Facility deletion requests — authenticated facility users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/dpa/deletion-requests', [\App\Http\Controllers\Api\V1\DeletionRequestController::class, 'store']);
    Route::post('/dpa/export-requests', [\App\Http\Controllers\Api\V1\DataExportController::class, 'store']);
    Route::get('/dpa/export-requests/{ulid}/download', [\App\Http\Controllers\Api\V1\DataExportController::class, 'download']);
});

// Patient DSAR — no auth (OTP verified inside controller)
Route::post('/dpa/patient-dsar', [\App\Http\Controllers\Api\V1\PatientDsarController::class, 'store']);

// Admin routes — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/dpa/retention-policies', [\App\Http\Controllers\Api\V1\DataRetentionController::class, 'index']);
    Route::patch('/admin/dpa/retention-policies/{id}', [\App\Http\Controllers\Api\V1\DataRetentionController::class, 'update']);
    Route::get('/admin/dpa/anonymisation-log', [\App\Http\Controllers\Api\V1\DataRetentionController::class, 'anonymisationLog']);
    Route::get('/admin/dpa/deletion-requests', [\App\Http\Controllers\Api\V1\DeletionRequestController::class, 'index']);
    Route::patch('/admin/dpa/deletion-requests/{ulid}/approve', [\App\Http\Controllers\Api\V1\DeletionRequestController::class, 'approve']);
    Route::patch('/admin/dpa/deletion-requests/{ulid}/reject', [\App\Http\Controllers\Api\V1\DeletionRequestController::class, 'reject']);
    Route::get('/admin/dpa/export-requests', [\App\Http\Controllers\Api\V1\DataExportController::class, 'index']);
    Route::patch('/admin/dpa/export-requests/{ulid}/approve', [\App\Http\Controllers\Api\V1\DataExportController::class, 'approve']);
    Route::get('/admin/dpa/patient-dsar', [\App\Http\Controllers\Api\V1\PatientDsarController::class, 'index']);
    Route::patch('/admin/dpa/patient-dsar/{ulid}/approve', [\App\Http\Controllers\Api\V1\PatientDsarController::class, 'approve']);
});

// -------------------------------------------------------
// Module 1 — Facility Onboarding & Profile
// -------------------------------------------------------

// Public registration — authenticated user registers their facility
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/facilities/register', [\App\Http\Controllers\Api\V1\FacilityController::class, 'register']);
    Route::get('/facilities', [\App\Http\Controllers\Api\V1\FacilityController::class, 'index']);
    Route::get('/facilities/{ulid}', [\App\Http\Controllers\Api\V1\FacilityController::class, 'show']);
    Route::patch('/facilities/{ulid}/link-account', [\App\Http\Controllers\Api\V1\FacilityController::class, 'linkAccount']);
    Route::post('/facilities/{ulid}/verify-ppb', [\App\Http\Controllers\Api\V1\FacilityController::class, 'verifyPpb']);
});

// Facility status — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::patch('/facilities/{ulid}/status', [\App\Http\Controllers\Api\V1\FacilityController::class, 'updateStatus']);
});

// GPS routes
Route::middleware(['auth:sanctum', 'role:network_admin|network_field_agent'])->group(function () {
    Route::patch('/facilities/{ulid}/gps', [\App\Http\Controllers\Api\V1\FacilityGpsController::class, 'update']);
    Route::get('/admin/facilities/gps-pending', [\App\Http\Controllers\Api\V1\FacilityGpsController::class, 'gpsPending']);
    Route::post('/admin/facilities/gps-bulk-upload', [\App\Http\Controllers\Api\V1\FacilityGpsController::class, 'bulkUpload']);
});

// Group management — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::post('/admin/groups', [\App\Http\Controllers\Api\V1\GroupController::class, 'store']);
    Route::post('/admin/groups/{groupUlid}/members', [\App\Http\Controllers\Api\V1\GroupController::class, 'addMember']);
    Route::delete('/admin/groups/{groupUlid}/members/{facilityUlid}', [\App\Http\Controllers\Api\V1\GroupController::class, 'removeMember']);
});

// Authorised placers — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/facilities/{ulid}/authorised-placers', [\App\Http\Controllers\Api\V1\AuthorisedPlacerController::class, 'index']);
    Route::post('/admin/facilities/{ulid}/authorised-placers', [\App\Http\Controllers\Api\V1\AuthorisedPlacerController::class, 'store']);
    Route::delete('/admin/facilities/{ulid}/authorised-placers/{userId}', [\App\Http\Controllers\Api\V1\AuthorisedPlacerController::class, 'destroy']);
});

// PPB Registry — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::post('/admin/ppb-registry/upload', [\App\Http\Controllers\Api\V1\PpbRegistryController::class, 'upload']);
    Route::get('/admin/ppb-registry/status', [\App\Http\Controllers\Api\V1\PpbRegistryController::class, 'status']);
    Route::get('/admin/ppb-registry/uploads', [\App\Http\Controllers\Api\V1\PpbRegistryController::class, 'uploads']);
    Route::get('/admin/ppb-registry/search', [\App\Http\Controllers\Api\V1\PpbRegistryController::class, 'search']);
});

// -------------------------------------------------------
// Module 2 — Product Catalogue & Order Placement
// -------------------------------------------------------

// Catalogue — authenticated users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/catalogue', [\App\Http\Controllers\Api\V1\CatalogueController::class, 'index']);
    Route::get('/catalogue/sync', [\App\Http\Controllers\Api\V1\CatalogueController::class, 'sync']);
    Route::get('/catalogue/categories', [\App\Http\Controllers\Api\V1\CatalogueController::class, 'categories']);
});

// Orders
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'store']);
    Route::post('/orders/sync', [\App\Http\Controllers\Api\V1\OrderController::class, 'sync']);
    Route::get('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
    Route::get('/orders/{ulid}', [\App\Http\Controllers\Api\V1\OrderController::class, 'show']);
    Route::delete('/orders/{ulid}', [\App\Http\Controllers\Api\V1\OrderController::class, 'cancel']);
});

// Saved carts
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/saved-carts', [\App\Http\Controllers\Api\V1\SavedCartController::class, 'index']);
    Route::post('/saved-carts', [\App\Http\Controllers\Api\V1\SavedCartController::class, 'store']);
    Route::get('/saved-carts/{ulid}/load', [\App\Http\Controllers\Api\V1\SavedCartController::class, 'load']);
    Route::delete('/saved-carts/{ulid}', [\App\Http\Controllers\Api\V1\SavedCartController::class, 'destroy']);
});

// Favourites
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/favourites', [\App\Http\Controllers\Api\V1\FavouriteController::class, 'index']);
    Route::post('/favourites/{productId}', [\App\Http\Controllers\Api\V1\FavouriteController::class, 'store']);
    Route::delete('/favourites/{productId}', [\App\Http\Controllers\Api\V1\FavouriteController::class, 'destroy']);
});

// Restock subscriptions
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/restock-subscriptions/{productId}', [\App\Http\Controllers\Api\V1\RestockSubscriptionController::class, 'store']);
    Route::delete('/restock-subscriptions/{productId}', [\App\Http\Controllers\Api\V1\RestockSubscriptionController::class, 'destroy']);
});

// Product requests
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/product-requests', [\App\Http\Controllers\Api\V1\ProductRequestController::class, 'store']);
    Route::get('/product-requests', [\App\Http\Controllers\Api\V1\ProductRequestController::class, 'index']);
});

// Product requests — admin
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/product-requests', [\App\Http\Controllers\Api\V1\ProductRequestController::class, 'adminIndex']);
    Route::patch('/admin/product-requests/{ulid}/action', [\App\Http\Controllers\Api\V1\ProductRequestController::class, 'action']);
});

// -------------------------------------------------------
// Module 3 — Delivery Confirmation & Dispute Module
// -------------------------------------------------------

// Delivery — logistics facility marks delivered
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/deliveries/{orderUlid}/mark-delivered', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'markDelivered']);
    Route::post('/deliveries/split/{splitLineId}/mark-delivered', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'markDeliveredSplit']);
    Route::post('/deliveries/{orderUlid}/confirm', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'confirm']);
    Route::post('/deliveries/split/{splitLineId}/confirm', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'confirmSplit']);
    Route::post('/deliveries/{orderUlid}/dispute', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'dispute']);
    Route::post('/deliveries/split/{splitLineId}/dispute', [\App\Http\Controllers\Api\V1\DeliveryController::class, 'disputeSplit']);
});

// Disputes — network admin and field agent
Route::middleware(['auth:sanctum', 'role:network_admin|network_field_agent'])->group(function () {
    Route::get('/disputes', [\App\Http\Controllers\Api\V1\DisputeController::class, 'index']);
    Route::patch('/disputes/{id}/resolve', [\App\Http\Controllers\Api\V1\DisputeController::class, 'resolve']);
});

// -------------------------------------------------------
// Module 4 — Wholesale Facility Portal
// -------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:wholesale_facility|network_admin'])->group(function () {
    Route::get('/wholesale/orders', [\App\Http\Controllers\Api\V1\WholesaleOrderController::class, 'index']);
    Route::patch('/wholesale/orders/{ulid}/status', [\App\Http\Controllers\Api\V1\WholesaleOrderController::class, 'updateStatus']);
    Route::post('/wholesale/orders/{ulid}/dispatch', [\App\Http\Controllers\Api\V1\WholesaleOrderController::class, 'dispatch']);
    Route::post('/wholesale/orders/split/{splitId}/dispatch', [\App\Http\Controllers\Api\V1\WholesaleOrderController::class, 'dispatchSplit']);
    Route::get('/wholesale/price-lists', [\App\Http\Controllers\Api\V1\WholesalePriceListController::class, 'index']);
    Route::post('/wholesale/price-lists', [\App\Http\Controllers\Api\V1\WholesalePriceListController::class, 'store']);
    Route::patch('/wholesale/price-lists/{id}', [\App\Http\Controllers\Api\V1\WholesalePriceListController::class, 'update']);
    Route::delete('/wholesale/price-lists/{id}', [\App\Http\Controllers\Api\V1\WholesalePriceListController::class, 'destroy']);
    Route::post('/wholesale/stock-status', [\App\Http\Controllers\Api\V1\WholesaleStockController::class, 'bulkUpdate']);
    Route::get('/wholesale/performance', [\App\Http\Controllers\Api\V1\WholesaleStockController::class, 'performance']);
});

// -------------------------------------------------------
// Module 5 — Network Coordination & Analytics Dashboard
// -------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:network_admin|network_field_agent'])->group(function () {
    Route::get('/network/dashboard/summary', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'summary']);
    Route::get('/network/facilities', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'facilities']);
    Route::get('/network/gmv', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'gmv']);
    Route::get('/network/audit-log', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'auditLog']);
    Route::get('/network/groups/{groupUlid}/performance', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'groupPerformance']);
    Route::post('/network/facilities/{ulid}/flag', [\App\Http\Controllers\Api\V1\FacilityFlagController::class, 'store']);
    Route::get('/network/flags', [\App\Http\Controllers\Api\V1\FacilityFlagController::class, 'index']);
    Route::patch('/network/flags/{id}/resolve', [\App\Http\Controllers\Api\V1\FacilityFlagController::class, 'resolve']);
});

Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/network/credit-health', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'creditHealth']);
    Route::get('/network/membership-comparison', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'membershipComparison']);
    Route::post('/network/reports/export', [\App\Http\Controllers\Api\V1\NetworkDashboardController::class, 'export']);
});

// -------------------------------------------------------
// Module 14 — Pharmacovigilance & Quality Reporting
// -------------------------------------------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/quality-flags', [\App\Http\Controllers\Api\V1\QualityFlagController::class, 'store'])->middleware('anonymise.flag');
    Route::get('/quality-flags/my-flags', [\App\Http\Controllers\Api\V1\QualityFlagController::class, 'myFlags'])->middleware('anonymise.flag');
});

Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/quality-flags', [\App\Http\Controllers\Api\V1\AdminQualityFlagController::class, 'index']);
    Route::patch('/admin/quality-flags/{ulid}/review', [\App\Http\Controllers\Api\V1\AdminQualityFlagController::class, 'review']);
    Route::post('/admin/quality-flags/{ulid}/confirm', [\App\Http\Controllers\Api\V1\AdminQualityFlagController::class, 'confirm']);
    Route::post('/admin/quality-flags/{ulid}/dismiss', [\App\Http\Controllers\Api\V1\AdminQualityFlagController::class, 'dismiss']);
});

// -------------------------------------------------------
// Module 22 — Recruiter Firm Module
// -------------------------------------------------------
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    // Firm management
    Route::get('/admin/recruiter/firms', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'index']);
    Route::post('/admin/recruiter/firms', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'store']);
    Route::patch('/admin/recruiter/firms/{id}', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'update']);
    Route::get('/admin/recruiter/firms/{id}/ledger', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'ledger']);
    Route::get('/admin/recruiter/firms/{id}/activations', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'activations']);
    Route::patch('/admin/recruiter/activations/{id}/reconcile', [\App\Http\Controllers\Api\V1\RecruiterFirmController::class, 'reconcile']);

    // Agent tree management
    Route::get('/admin/recruiter/firms/{firmId}/agents', [\App\Http\Controllers\Api\V1\RecruiterAgentController::class, 'index']);
    Route::post('/admin/recruiter/firms/{firmId}/agents', [\App\Http\Controllers\Api\V1\RecruiterAgentController::class, 'store']);
    Route::patch('/admin/recruiter/agents/{id}', [\App\Http\Controllers\Api\V1\RecruiterAgentController::class, 'update']);
    Route::patch('/admin/recruiter/agents/{id}/deactivate', [\App\Http\Controllers\Api\V1\RecruiterAgentController::class, 'deactivate']);
});

// -------------------------------------------------------
// Module 23 — WhatsApp Ordering Integration
// -------------------------------------------------------

// Webhook — no auth, no CSRF, Meta IP whitelist only
Route::match(['get', 'post'], '/whatsapp/webhook', [
    \App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'webhook'
])->middleware('whatsapp.whitelist');

// Status — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/whatsapp/status', [\App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'status']);
});

// -------------------------------------------------------
// Module 24 — Security Enhancement Layer
// -------------------------------------------------------

// MFA — authenticated users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/mfa/request', [\App\Http\Controllers\Api\V1\MfaController::class, 'requestOtp']);
    Route::post('/mfa/verify', [\App\Http\Controllers\Api\V1\MfaController::class, 'verifyOtp']);
    Route::post('/mfa/verify-backup', [\App\Http\Controllers\Api\V1\MfaController::class, 'verifyBackupCode']);
    Route::post('/mfa/backup-codes/generate', [\App\Http\Controllers\Api\V1\MfaController::class, 'generateBackupCodes']);
    Route::get('/mfa/backup-codes/count', [\App\Http\Controllers\Api\V1\MfaController::class, 'backupCodeCount']);
});

// Security events — network_admin only
Route::middleware(['auth:sanctum', 'role:network_admin'])->group(function () {
    Route::get('/admin/security/events', [\App\Http\Controllers\Api\V1\SecurityEventController::class, 'index']);
    Route::patch('/admin/security/events/{id}/resolve', [\App\Http\Controllers\Api\V1\SecurityEventController::class, 'resolve']);
    Route::get('/admin/security/summary', [\App\Http\Controllers\Api\V1\SecurityEventController::class, 'summary']);
    Route::post('/admin/users/{id}/mfa/disable', [\App\Http\Controllers\Api\V1\AdminMfaController::class, 'disable']);
});
