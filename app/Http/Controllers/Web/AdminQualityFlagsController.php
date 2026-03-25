<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminQualityFlagsController extends Controller
{
    public function index()
    {
        $flags = DB::table('quality_flags as qf')
            ->join('products as p', 'qf.product_id', '=', 'p.id')
            ->join('facilities as f', 'qf.facility_id', '=', 'f.id')
            ->select(['qf.*', 'p.generic_name', 'p.sku_code', 'f.facility_name', 'f.county'])
            ->orderByRaw("FIELD(qf.status,'OPEN','UNDER_REVIEW','CONFIRMED','DISMISSED')")
            ->orderBy('qf.created_at', 'desc')
            ->paginate(20);

        $stats = [
            'open'         => DB::table('quality_flags')->where('status','OPEN')->count(),
            'under_review' => DB::table('quality_flags')->where('status','UNDER_REVIEW')->count(),
            'confirmed'    => DB::table('quality_flags')->where('status','CONFIRMED')->count(),
        ];

        return view('admin.quality-flags', compact('flags', 'stats'));
    }
}
