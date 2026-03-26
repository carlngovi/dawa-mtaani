<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticsRoutesController extends Controller
{
    public function index()
    {
        $orders = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereIn('o.status', ['PACKED', 'DISPATCHED'])
            ->whereNull('o.deleted_at')
            ->select([
                'o.id', 'o.ulid', 'o.status', 'o.total_amount', 'o.created_at',
                'f.facility_name', 'f.county', 'f.sub_county', 'f.ward',
                'f.physical_address', 'f.latitude', 'f.longitude',
            ])
            ->orderBy('f.county')
            ->orderBy('o.created_at', 'desc')
            ->get();

        $byCounty = $orders->groupBy('county');

        $countyCentroids = [
            'Nairobi' => ['lat' => -1.2921, 'lng' => 36.8219],
            'Mombasa' => ['lat' => -4.0435, 'lng' => 39.6682],
            'Kisumu' => ['lat' => -0.1022, 'lng' => 34.7617],
            'Nakuru' => ['lat' => -0.3031, 'lng' => 36.0800],
            'Kiambu' => ['lat' => -1.1713, 'lng' => 36.8353],
            'Machakos' => ['lat' => -1.5177, 'lng' => 37.2634],
            'Kajiado' => ['lat' => -1.8520, 'lng' => 36.7764],
            'Kilifi' => ['lat' => -3.5107, 'lng' => 39.8499],
            'Nyeri' => ['lat' => -0.4167, 'lng' => 36.9500],
            'Meru' => ['lat' => 0.0467, 'lng' => 37.6497],
            'Kisii' => ['lat' => -0.6817, 'lng' => 34.7667],
            'Kakamega' => ['lat' => 0.2827, 'lng' => 34.7519],
            'Kericho' => ['lat' => -0.3686, 'lng' => 35.2863],
            'Uasin Gishu' => ['lat' => 0.5167, 'lng' => 35.2667],
            'Bungoma' => ['lat' => 0.5635, 'lng' => 34.5606],
            'Embu' => ['lat' => -0.5300, 'lng' => 37.4500],
            'Kitui' => ['lat' => -1.3667, 'lng' => 38.0167],
            'Makueni' => ['lat' => -1.8035, 'lng' => 37.6237],
            'Kwale' => ['lat' => -4.1740, 'lng' => 39.4521],
        ];

        $warehouse = ['lat' => -1.2921, 'lng' => 36.8219, 'name' => 'NILA Pharmaceuticals Warehouse'];

        $mapOrders = $orders->map(function ($o) use ($countyCentroids) {
            $lat = $o->latitude ?? null;
            $lng = $o->longitude ?? null;
            $hasGps = true;
            if (! $lat || ! $lng) {
                $c = $countyCentroids[$o->county] ?? ['lat' => -1.2921, 'lng' => 36.8219];
                $lat = $c['lat'];
                $lng = $c['lng'];
                $hasGps = false;
            }
            return [
                'ulid' => $o->ulid, 'status' => $o->status,
                'facility_name' => $o->facility_name, 'county' => $o->county,
                'amount' => (float) $o->total_amount,
                'lat' => (float) $lat, 'lng' => (float) $lng, 'has_gps' => $hasGps,
            ];
        })->values();

        $warehouseJson = json_encode($warehouse);
        $mapOrdersJson = json_encode($mapOrders);
        $googleMapsKey = config('services.google_maps.key');

        return view('logistics.routes', compact(
            'orders', 'byCounty', 'countyCentroids', 'warehouse',
            'warehouseJson', 'mapOrdersJson', 'googleMapsKey'
        ));
    }
}
