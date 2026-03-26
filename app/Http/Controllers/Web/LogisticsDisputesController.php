<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogisticsDisputesController extends Controller
{
    public function index(Request $request)
    {
        $disputes = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->join('orders as o', 'dc.order_id', '=', 'o.id')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereNull('o.deleted_at')
            ->select([
                'dd.id', 'dd.reason', 'dd.status',
                'dd.raised_at', 'dd.sla_deadline_at', 'dd.sla_breached',
                'o.ulid as order_ulid', 'f.facility_name', 'f.county',
            ])
            ->when($request->filled('status'), fn($q) => $q->where('dd.status', $request->status))
            ->orderByRaw("CASE WHEN dd.status='OPEN' THEN 0 WHEN dd.status='UNDER_REVIEW' THEN 1 ELSE 2 END")
            ->orderBy('dd.sla_deadline_at', 'asc')
            ->paginate(20)->withQueryString();

        $stats = [
            'open'     => DB::table('delivery_disputes')->where('status', 'OPEN')->count(),
            'breached' => DB::table('delivery_disputes')->where('status', 'OPEN')->where('sla_breached', true)->count(),
            'resolved' => DB::table('delivery_disputes')->where('status', 'RESOLVED')
                            ->whereMonth('resolved_at', now()->month)->count(),
        ];

        return view('logistics.disputes', compact('disputes', 'stats'));
    }
}
