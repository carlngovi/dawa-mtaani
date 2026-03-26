<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminPaymentsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->hasAnyRole([
            'network_admin', 'admin', 'super_admin', 'technical_admin', 'shared_accountant',
        ]), 403);

        $currency    = CurrencyConfig::get();
        $isTechAdmin = $user->hasRole('technical_admin');

        $instructions = collect();
        $stats        = ['pending' => 0, 'processing' => 0, 'manual_review' => 0, 'completed_today' => 0];
        $repayments   = collect();
        $copay        = ['failed' => 0, 'escalated' => 0];

        if (Schema::hasTable('payment_instructions')) {
            $query = DB::table('payment_instructions')
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->orderBy('created_at', 'desc');

            $instructions = $query->paginate(25)->withQueryString();

            $stats = [
                'pending'         => DB::table('payment_instructions')->where('status', 'PENDING')->count(),
                'processing'      => DB::table('payment_instructions')->where('status', 'PROCESSING')->count(),
                'manual_review'   => DB::table('payment_instructions')->where('status', 'MANUAL_REVIEW')->count(),
                'completed_today' => DB::table('payment_instructions')->where('status', 'COMPLETED')->whereDate('updated_at', today())->count(),
            ];
        }

        if (Schema::hasTable('repayment_records')) {
            $repayments = DB::table('repayment_records as rr')
                ->leftJoin('facilities as f', 'rr.facility_id', '=', 'f.id')
                ->select(['rr.*', 'f.facility_name'])
                ->orderBy('rr.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        if (Schema::hasTable('copay_payment_attempts')) {
            $copay = [
                'failed'    => DB::table('copay_payment_attempts')->where('status', 'FAILED')->count(),
                'escalated' => DB::table('copay_payment_attempts')->where('status', 'ESCALATED')->count(),
            ];
        }

        return view('admin.payments', compact('instructions', 'stats', 'repayments', 'copay', 'currency', 'isTechAdmin'));
    }
}
