<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'   => 'required|string|min:2',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $q   = $validated['q'];
        $lat = $validated['lat'] ?? null;
        $lng = $validated['lng'] ?? null;

        $cacheKey = 'store_search:' . md5("{$q}:{$lat}:{$lng}");

        $cacheTtl = (int) DB::table('system_settings')
            ->where('key', 'search_cache_minutes')
            ->value('value') ?: 15;

        $results = Cache::tags(['store_search'])->remember($cacheKey, $cacheTtl * 60, function () use ($q, $lat, $lng) {
            $query = DB::table('products as p')
                ->join('facility_stock_status as fss', 'fss.product_id', '=', 'p.id')
                ->join('online_store_eligible_facilities as osef', 'osef.facility_id', '=', 'fss.wholesale_facility_id')
                ->join('facilities as f', 'f.id', '=', 'fss.wholesale_facility_id')
                ->join('wholesale_price_lists as wpl', function ($join) {
                    $join->on('wpl.wholesale_facility_id', '=', 'fss.wholesale_facility_id')
                        ->on('wpl.product_id', '=', 'p.id')
                        ->where('wpl.is_active', true);
                })
                ->where('osef.is_active', true)
                ->where('fss.stock_status', 'IN_STOCK')
                ->where(function ($where) use ($q) {
                    $where->where('p.generic_name', 'LIKE', "%{$q}%")
                        ->orWhere('p.brand_name', 'LIKE', "%{$q}%");
                });

            $selects = [
                'p.ulid as product_ulid',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'wpl.unit_price',
                'f.ulid as facility_ulid',
                'f.facility_name',
                'osef.branding_mode',
                'osef.is_network_member',
                'fss.updated_at as stock_confirmed_at',
            ];

            if ($lat !== null && $lng !== null) {
                $latSafe = (float) $lat;
                $lngSafe = (float) $lng;
                $haversine = "6371 * ACOS(COS(RADIANS({$latSafe})) * COS(RADIANS(f.latitude)) * COS(RADIANS(f.longitude) - RADIANS({$lngSafe})) + SIN(RADIANS({$latSafe})) * SIN(RADIANS(f.latitude)))";
                $selects[] = DB::raw("({$haversine}) as distance_km");
                $query->orderBy('distance_km', 'asc');
            }

            $query->select($selects);

            return $query->get()->map(function ($row) use ($lat, $lng) {
                return [
                    'product_ulid'           => $row->product_ulid,
                    'generic_name'           => $row->generic_name,
                    'brand_name'             => $row->brand_name,
                    'unit_size'              => $row->unit_size,
                    'unit_price'             => CurrencyConfig::format((float) $row->unit_price),
                    'facility_ulid'          => $row->facility_ulid,
                    'display_name'           => $row->branding_mode === 'DAWA_MTAANI'
                        ? 'Dawa Mtaani'
                        : $row->facility_name,
                    'branding_mode'          => $row->branding_mode,
                    'verified_badge_eligible' => (bool) $row->is_network_member,
                    'stock_confirmed_at'     => $row->stock_confirmed_at,
                    'distance_km'            => ($lat !== null && $lng !== null)
                        ? round((float) $row->distance_km, 1)
                        : null,
                ];
            })->all();
        });

        // Fallback: if no stock-linked results, search products catalogue directly
        if (empty($results)) {
            $fallback = DB::table('products as p')
                ->where('p.is_active', true)
                ->where(function ($where) use ($q) {
                    $where->where('p.generic_name', 'LIKE', "%{$q}%")
                          ->orWhere('p.brand_name', 'LIKE', "%{$q}%")
                          ->orWhere('p.sku_code', 'LIKE', "%{$q}%");
                })
                ->select([
                    'p.id as product_id',
                    'p.ulid as product_ulid',
                    'p.generic_name',
                    'p.brand_name',
                    'p.unit_size',
                    'p.therapeutic_category',
                ])
                ->limit(20)
                ->get()
                ->map(function ($row) {
                    return [
                        'product_id'             => $row->product_id,
                        'product_ulid'           => $row->product_ulid,
                        'generic_name'           => $row->generic_name,
                        'brand_name'             => $row->brand_name ?? '',
                        'unit_size'              => $row->unit_size,
                        'therapeutic_category'   => $row->therapeutic_category,
                        'unit_price'             => 'Price on request',
                        'facility_ulid'          => null,
                        'display_name'           => 'Catalogue',
                        'branding_mode'          => null,
                        'verified_badge_eligible' => false,
                        'stock_confirmed_at'     => null,
                        'distance_km'            => null,
                    ];
                })->all();

            return response()->json([
                'status'  => 'success',
                'data'    => $fallback,
                'message' => 'Showing catalogue results — no live stock data available.',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => $results,
            'message' => '',
        ]);
    }

    public function facilityStock(string $ulid): JsonResponse
    {
        $facility = DB::table('facilities')->where('ulid', $ulid)->first();

        if (! $facility) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Facility not found.',
            ], 404);
        }

        $products = DB::table('facility_stock_status as fss')
            ->join('products as p', 'p.id', '=', 'fss.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) {
                $join->on('wpl.wholesale_facility_id', '=', 'fss.wholesale_facility_id')
                    ->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.is_active', true);
            })
            ->where('fss.wholesale_facility_id', $facility->id)
            ->where('fss.stock_status', 'IN_STOCK')
            ->select([
                'p.ulid as product_ulid',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'wpl.unit_price',
                'fss.updated_at as stock_confirmed_at',
            ])
            ->get()
            ->map(function ($row) {
                return [
                    'product_ulid'       => $row->product_ulid,
                    'generic_name'       => $row->generic_name,
                    'brand_name'         => $row->brand_name,
                    'unit_size'          => $row->unit_size,
                    'unit_price'         => CurrencyConfig::format((float) $row->unit_price),
                    'stock_confirmed_at' => $row->stock_confirmed_at,
                ];
            })
            ->all();

        return response()->json([
            'status'  => 'success',
            'data'    => $products,
            'message' => '',
        ]);
    }
}
