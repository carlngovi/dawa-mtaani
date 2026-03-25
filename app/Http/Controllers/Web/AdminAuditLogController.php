<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->leftJoin('facilities as f', 'al.facility_id', '=', 'f.id')
            ->select(['al.*', 'u.name as user_name', 'f.facility_name'])
            ->orderBy('al.created_at', 'desc');

        if ($request->filled('action'))      $query->where('al.action', 'like', '%'.$request->action.'%');
        if ($request->filled('facility_id')) $query->where('al.facility_id', $request->facility_id);
        if ($request->filled('date_from'))   $query->where('al.created_at', '>=', $request->date_from);

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.audit-log', compact('logs'));
    }
}
