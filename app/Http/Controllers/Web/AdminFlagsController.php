<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminFlagsController extends Controller
{
    public function index()
    {
        $flags = DB::table('facility_flags as ff')
            ->join('facilities as f', 'ff.facility_id', '=', 'f.id')
            ->join('users as u', 'ff.flagged_by', '=', 'u.id')
            ->select(['ff.*', 'f.facility_name', 'f.county', 'u.name as flagged_by_name'])
            ->whereNull('ff.resolved_at')
            ->orderBy('ff.created_at', 'desc')
            ->paginate(30);

        $stats = [
            'open'     => DB::table('facility_flags')->whereNull('resolved_at')->count(),
            'resolved' => DB::table('facility_flags')->whereNotNull('resolved_at')->count(),
        ];

        return view('admin.flags', compact('flags', 'stats'));
    }
}
