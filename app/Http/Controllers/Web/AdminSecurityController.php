<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSecurityController extends Controller
{
    public function index()
    {
        $events = DB::table('security_events as se')
            ->leftJoin('users as u', 'se.user_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'se.facility_id', '=', 'f.id')
            ->select(['se.*', 'u.name as user_name', 'u.email as user_email', 'f.facility_name'])
            ->orderBy('se.created_at', 'desc')
            ->paginate(30);

        $summary = [
            'critical_unresolved' => DB::table('security_events')->where('severity','CRITICAL')->whereNull('resolved_at')->count(),
            'high_unresolved'     => DB::table('security_events')->where('severity','HIGH')->whereNull('resolved_at')->count(),
            'last_24h'            => DB::table('security_events')->where('created_at', '>=', Carbon::now()->subHours(24))->count(),
        ];

        return view('admin.security', compact('events', 'summary'));
    }
}
