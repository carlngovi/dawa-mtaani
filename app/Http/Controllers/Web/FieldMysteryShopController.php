<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldMysteryShopController extends Controller
{
    public function index()
    {
        $county = Auth::user()->county;

        $facilities = DB::table('facilities')
            ->where('county', $county)
            ->where('facility_status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->select(['id', 'ulid', 'facility_name', 'ward'])
            ->orderBy('facility_name')
            ->get();

        $recentVisits = collect();
        if (Schema::hasTable('mystery_shop_visits')) {
            $recentVisits = DB::table('mystery_shop_visits as m')
                ->join('facilities as f', 'm.facility_id', '=', 'f.id')
                ->where('f.county', $county)
                ->select(['m.id', 'm.visited_at', 'm.overall_score', 'f.facility_name', 'f.ward'])
                ->orderBy('m.visited_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('field.mystery-shop', compact('facilities', 'recentVisits', 'county'));
    }
}
