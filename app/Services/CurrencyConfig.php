<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CurrencyConfig
{
    public static function get(): array
    {
        return Cache::remember('currency_config', 300, function () {
            $keys = [
                'currency_iso_code',
                'currency_symbol',
                'currency_decimal_places',
                'grant_exchange_rate',
                'grant_base_currency',
                'display_timezone',
            ];

            $settings = DB::table('system_settings')
                ->whereIn('key', $keys)
                ->pluck('value', 'key');

            return [
                'iso_code' => $settings->get('currency_iso_code', 'KES'),
                'symbol' => $settings->get('currency_symbol', 'KES'),
                'decimal_places' => (int) $settings->get('currency_decimal_places', 2),
                'grant_rate' => (float) $settings->get('grant_exchange_rate', 127),
                'grant_currency' => $settings->get('grant_base_currency', 'USD'),
                'display_timezone' => $settings->get('display_timezone', 'Africa/Nairobi'),
            ];
        });
    }

    public static function format(float $amount): string
    {
        $config = self::get();
        $rounded = round($amount, $config['decimal_places']);

        return $config['symbol'] . ' ' . number_format($rounded, $config['decimal_places']);
    }

    public static function clearCache(): void
    {
        Cache::forget('currency_config');
    }
}
