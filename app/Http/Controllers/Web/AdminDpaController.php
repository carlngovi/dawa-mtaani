<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDpaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole([
            'network_admin', 'admin', 'super_admin', 'technical_admin', 'assistant_admin',
        ]), 403);

        $deletionRequests = DB::table('data_deletion_requests')->orderByDesc('created_at')->paginate(20, ['*'], 'delete_page');
        $exportRequests = DB::table('data_export_requests')->orderByDesc('created_at')->paginate(20, ['*'], 'export_page');
        $retentionPolicies = DB::table('data_retention_policies')->orderBy('data_category')->get();
        $anonymisationLog = DB::table('anonymisation_log')->orderByDesc('started_at')->limit(50)->get();
        $stats = [
            'pending_deletions' => DB::table('data_deletion_requests')->where('status', 'PENDING')->count(),
            'pending_exports' => DB::table('data_export_requests')->where('status', 'PENDING')->count(),
            'total_anonymised' => DB::table('anonymisation_log')->count(),
            'policies' => DB::table('data_retention_policies')->count(),
        ];

        return view('admin.dpa', compact('deletionRequests', 'exportRequests', 'retentionPolicies', 'anonymisationLog', 'stats'));
    }
}
