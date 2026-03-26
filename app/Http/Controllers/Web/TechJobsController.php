<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TechJobsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['technical_admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        // Latest run per job name
        $jobHealth = DB::table('job_monitoring as j1')
            ->whereRaw('j1.started_at = (
                SELECT MAX(j2.started_at)
                FROM job_monitoring j2
                WHERE j2.job_name = j1.job_name
            )')
            ->select(['job_name', 'status', 'started_at', 'completed_at', 'duration_ms', 'error_message'])
            ->orderBy('job_name')
            ->get();

        // Failure count per job in last 7 days
        $failureCounts = DB::table('job_monitoring')
            ->where('status', 'FAILED')
            ->where('started_at', '>=', now()->subDays(7))
            ->selectRaw('job_name, COUNT(*) as failure_count')
            ->groupBy('job_name')
            ->pluck('failure_count', 'job_name');

        $failedJobs  = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(20)->get();
        $pendingJobs = DB::table('jobs')->count();

        return view('tech.jobs', compact('jobHealth', 'failureCounts', 'failedJobs', 'pendingJobs'));
    }
}
