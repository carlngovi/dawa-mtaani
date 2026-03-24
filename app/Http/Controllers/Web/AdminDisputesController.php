<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminDisputesController extends Controller
{
    public function index()
    {
        $disputes = DB::table('delivery_disputes as dd')
            ->join('delivery_confirmations as dc', 'dd.delivery_confirmation_id', '=', 'dc.id')
            ->leftJoin('orders as o', 'dc.order_id', '=', 'o.id')
            ->leftJoin('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->select(['dd.*', 'o.ulid as order_ulid', 'f.facility_name', 'f.county'])
            ->orderByRaw("FIELD(dd.status,'OPEN','UNDER_REVIEW','RESOLVED')")
            ->orderBy('dd.sla_deadline_at')
            ->paginate(20);

        $stats = [
            'open'         => DB::table('delivery_disputes')->where('status','OPEN')->count(),
            'under_review' => DB::table('delivery_disputes')->where('status','UNDER_REVIEW')->count(),
            'sla_breached' => DB::table('delivery_disputes')->where('sla_breached',true)->where('status','!=','RESOLVED')->count(),
        ];

        return view('admin.disputes', compact('disputes', 'stats'));
    }
}
