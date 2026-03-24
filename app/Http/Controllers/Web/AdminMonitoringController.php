<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMonitoringController extends Controller
{
    public function index()
    {
        $jobs = DB::table('job_monitoring')
            ->select('job_name', DB::raw('MAX(started_at) as last_run_at'),
                     DB::raw('MAX(status) as last_status'),
                     DB::raw('AVG(duration_ms) as avg_duration_ms'),
                     DB::raw('COUNT(CASE WHEN status = "FAILED" THEN 1 END) as fail_count'))
            ->groupBy('job_name')
            ->orderBy('job_name')
            ->get()
            ->map(function ($job) {
                $job->short_name = class_basename($job->job_name);
                return $job;
            });

        $activeAlerts = DB::table('business_metric_alerts')
            ->whereNull('acknowledged_at')
            ->orderByRaw("FIELD(severity,'CRITICAL','WARNING','INFO')")
            ->orderBy('created_at', 'desc')
            ->get();

        $sloRecords = DB::table('slo_compliance_records')
            ->orderBy('period_start', 'desc')
            ->limit(30)
            ->get()
            ->groupBy('sli_name');

        return view('admin.monitoring', compact('jobs', 'activeAlerts', 'sloRecords'));
    }
}
