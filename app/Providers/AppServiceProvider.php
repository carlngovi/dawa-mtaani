<?php

namespace App\Providers;

use App\Contracts\BankingPartyInterface;
use App\Contracts\CourierProviderInterface;
use App\Contracts\PpbRegistryInterface;
use App\Contracts\SmsGatewayInterface;
use App\Services\Integrations\AfricasTalkingService;
use App\Services\Integrations\IMBankingService;
use App\Services\Integrations\PpbRegistryFileService;
use App\Services\Integrations\SgaLogisticsService;
use App\Models\CreditTier;
use App\Models\CreditTranche;
use App\Models\CreditTrancheParty;
use App\Models\FacilityStockStatus;
use App\Observers\CreditTierObserver;
use App\Observers\CreditTrancheObserver;
use App\Observers\CreditTranchePartyObserver;
use App\Observers\FacilityStockStatusObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BankingPartyInterface::class, IMBankingService::class);
        $this->app->bind(CourierProviderInterface::class, SgaLogisticsService::class);
        $this->app->bind(SmsGatewayInterface::class, AfricasTalkingService::class);

        $this->app->bind(PpbRegistryInterface::class, function () {
            $mode = DB::table('system_settings')
                ->where('key', 'ppb_verification_mode')
                ->value('value') ?? 'FILE';

            return $mode === 'API'
                ? app(\App\Services\Integrations\PpbRegistryApiService::class)
                : app(\App\Services\Integrations\PpbRegistryFileService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FacilityStockStatus::observe(FacilityStockStatusObserver::class);
        CreditTranche::observe(CreditTrancheObserver::class);
        CreditTier::observe(CreditTierObserver::class);
        CreditTrancheParty::observe(CreditTranchePartyObserver::class);

        $carbon = \Carbon\Carbon::class;
        $carbon::macro('toEAT', function () {
            return $this->setTimezone(
                \Illuminate\Support\Facades\DB::table('system_settings')
                    ->where('key', 'display_timezone')
                    ->value('value') ?? 'Africa/Nairobi'
            );
        });
    }
}
