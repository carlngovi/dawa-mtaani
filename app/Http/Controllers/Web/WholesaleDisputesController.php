<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * WholesaleDisputesController
 *
 * wholesale_facility, dispute inbox
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleDisputesController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = Auth::user()->facility_id;

        $disputes = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->join('orders as o', 'dc.order_id', '=', 'o.id')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereNull('o.deleted_at')
            ->whereExists(function ($sub) use ($facilityId) {
                $sub->select(DB::raw(1))
                    ->from('order_lines')
                    ->whereColumn('order_lines.order_id', 'o.id')
                    ->where('order_lines.wholesale_facility_id', $facilityId);
            })
            ->select([
                'dd.id', 'dd.reason', 'dd.notes', 'dd.status',
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

        return view('wholesale.disputes', compact('disputes', 'stats'));
    }
}
