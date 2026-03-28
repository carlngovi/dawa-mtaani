<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));

            Route::middleware('api')
                ->prefix('api/v2')
                ->group(base_path('routes/api_v2.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.spotter' => \App\Http\Middleware\AuthenticateSpotter::class,
            'facility.scope' => \App\Http\Middleware\EnsureFacilityScope::class,
            'anonymise.flag' => \App\Http\Middleware\AnonymiseQualityFlagResponse::class,
            'financial.timeout' => \App\Http\Middleware\FinancialSessionTimeout::class,
            'rate.limit' => \App\Http\Middleware\AdaptiveRateLimiter::class,
            'hmac.callback' => \App\Http\Middleware\VerifyHmacCallback::class,
            'mpesa.whitelist' => \App\Http\Middleware\MpesaIpWhitelist::class,
            'whatsapp.whitelist' => \App\Http\Middleware\WhatsAppIpWhitelist::class,
            'session.fingerprint' => \App\Http\Middleware\SessionFingerprintCheck::class,
            'feature' => \App\Http\Middleware\FeatureFlag::class,
            'api.version' => \App\Http\Middleware\ApiVersion::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'portal' => \App\Http\Middleware\EnforcePortalRole::class,
            'auth.sales_rep' => \App\Http\Middleware\EnsureSalesRep::class,
            'auth.county' => \App\Http\Middleware\EnsureCountyCoordinator::class,
        ]);

        // Unauthenticated web requests → /login; API requests → 401 JSON
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
