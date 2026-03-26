<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogisticsRoutesController extends Controller
{
    public function index()
    {
        $unassigned = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('o.status', 'PACKED')
            ->whereNull('o.deleted_at')
            ->select(['o.id', 'o.ulid', 'o.total_amount', 'f.facility_name', 'f.county', 'f.ward'])
            ->orderBy('f.county')->orderBy('f.ward')
            ->get();

        $byCounty = $unassigned->groupBy('county');

        return view('logistics.routes', compact('unassigned', 'byCounty'));
    }
}
