<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldDisputesController extends Controller
{
    public function index(Request $request)
    {
        $county = Auth::user()->county;

        $disputes = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->join('orders as o', 'dc.order_id', '=', 'o.id')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('f.county', $county)
            ->where('dd.sla_breached', true)
            ->whereNull('o.deleted_at')
            ->select([
                'dd.id', 'dd.reason', 'dd.notes', 'dd.status', 'dd.raised_at',
                'o.ulid as order_ulid', 'f.facility_name',
            ])
            ->orderBy('dd.raised_at', 'asc')
            ->paginate(20)->withQueryString();

        return view('field.disputes', compact('disputes', 'county'));
    }
}
