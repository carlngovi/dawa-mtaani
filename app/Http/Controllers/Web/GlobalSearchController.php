<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $like    = '%' . $query . '%';
        $results = [];

        // Facilities
        $facilities = DB::table('facilities')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($like) {
                $q->where('facility_name', 'like', $like)
                  ->orWhere('ppb_licence_number', 'like', $like)
                  ->orWhere('phone', 'like', $like);
            })
            ->select('ulid', 'facility_name', 'ppb_facility_type', 'facility_status', 'county')
            ->limit(5)
            ->get();

        foreach ($facilities as $f) {
            $results[] = [
                'type'     => 'Facility',
                'label'    => $f->facility_name,
                'sublabel' => $f->ppb_facility_type . ' · ' . $f->county,
                'status'   => $f->facility_status,
                'url'      => '/admin/facilities/' . $f->ulid,
            ];
        }

        // Orders
        $orders = DB::table('orders')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($like) {
                $q->where('ulid', 'like', $like);
            })
            ->select('ulid', 'status', 'total_amount', 'created_at')
            ->limit(5)
            ->get();

        foreach ($orders as $o) {
            $results[] = [
                'type'     => 'Order',
                'label'    => 'Order #' . strtoupper(substr($o->ulid, -8)),
                'sublabel' => $o->status . ' · KES ' . number_format($o->total_amount, 2),
                'status'   => $o->status,
                'url'      => '/admin/orders?search=' . $o->ulid,
            ];
        }

        // Users
        $users = DB::table('users')
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('phone', 'like', $like);
            })
            ->select('id', 'name', 'email', 'phone')
            ->limit(5)
            ->get();

        foreach ($users as $u) {
            $results[] = [
                'type'     => 'User',
                'label'    => $u->name,
                'sublabel' => $u->email,
                'status'   => null,
                'url'      => '/admin/users?search=' . urlencode($u->email),
            ];
        }

        // Products
        $products = DB::table('products')
            ->where(function ($q) use ($like) {
                $q->where('generic_name', 'like', $like)
                  ->orWhere('sku_code', 'like', $like);
            })
            ->select('id', 'generic_name', 'sku_code', 'therapeutic_category')
            ->limit(5)
            ->get();

        foreach ($products as $p) {
            $results[] = [
                'type'     => 'Product',
                'label'    => $p->generic_name,
                'sublabel' => $p->sku_code . ' · ' . $p->therapeutic_category,
                'status'   => null,
                'url'      => '/admin/products?search=' . urlencode($p->generic_name),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
