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

class TechDiagnosticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['technical_admin', 'super_admin'])) {
            return redirect('/dashboard');
        }

        $integrations = [];
        $checks = [
            'M-Pesa Daraja' => function () {
                $key = config('daraja.consumer_key');
                $env = config('daraja.env', 'sandbox');
                if (empty($key)) return false;
                return strtoupper($env) . ' - CONFIGURED';
            },
            'I&M Bank API'  => fn() => DB::table('system_settings')->where('key', 'BANK_API_URL')->exists(),
            'SGA Courier'   => fn() => DB::table('system_settings')->where('key', 'SGA_WEBHOOK_SECRET')->exists(),
            'WhatsApp API'  => fn() => DB::table('system_settings')->where('key', 'WHATSAPP_API_TOKEN')->exists(),
            'PPB Registry'  => fn() => Schema::hasTable('ppb_registry_cache') && DB::table('ppb_registry_cache')->exists(),
            'Redis'         => function () {
                try {
                    Cache::store('redis')->put('healthcheck', 1, 1);
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            },
            'Queue Worker'  => fn() => config('queue.default') !== 'sync',
        ];
        foreach ($checks as $name => $check) {
            try {
                $result = $check();
                if (is_string($result)) {
                    $integrations[$name] = $result;
                } else {
                    $integrations[$name] = $result ? 'OK' : 'WARN';
                }
            } catch (\Exception $e) {
                $integrations[$name] = 'ERROR';
            }
        }

        $queueDepth = match(config('queue.default')) {
            'database' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            default    => 0,
        };
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        $queryResults = session('query_results');
        $querySQL     = session('query_sql');

        return view('tech.diagnostics', compact(
            'integrations', 'queueDepth', 'failedJobs', 'queryResults', 'querySQL'
        ));
    }

    public function query(Request $request)
    {
        if (! Auth::user()->hasRole('technical_admin')) abort(403);

        $sql = trim($request->input('sql', ''));
        if (! preg_match('/^\s*SELECT\s/i', $sql)) {
            return back()->with('error',
                'Only SELECT queries are permitted. Write operations require a T0 approval request at /tech/write.');
        }
        try {
            $results = DB::select(DB::raw($sql));
            session(['query_results' => $results, 'query_sql' => $sql]);
        } catch (\Exception $e) {
            return back()->with('error', 'Query error: ' . $e->getMessage());
        }
        return back();
    }
}
