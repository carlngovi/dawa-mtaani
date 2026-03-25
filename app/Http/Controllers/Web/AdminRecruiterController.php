<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRecruiterController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return redirect('/admin/dashboard');
        }
        $currency = CurrencyConfig::get();
        $firms = DB::table('recruiter_firms')->orderBy('firm_name')->get();
        $agentCounts = DB::table('recruiter_agents')->select('firm_id', DB::raw('COUNT(*) as total'))->where('status', 'ACTIVE')->groupBy('firm_id')->pluck('total', 'firm_id');
        $activationCounts = DB::table('recruiter_activation_events')->select('firm_id', DB::raw('COUNT(*) as total'))->groupBy('firm_id')->pluck('total', 'firm_id');
        $commissionTotals = DB::table('recruiter_ledger_entries')->select('firm_id', DB::raw('SUM(amount_kes) as total'))->groupBy('firm_id')->pluck('total', 'firm_id');

        $selectedFirmId = $request->get('firm_id');
        $selectedFirm = null; $agents = collect(); $activations = collect(); $ledger = collect();

        if ($selectedFirmId) {
            $selectedFirm = DB::table('recruiter_firms')->where('id', $selectedFirmId)->first();
            $agents = DB::table('recruiter_agents as a')->leftJoin('recruiter_agents as p', 'a.parent_agent_id', '=', 'p.id')->where('a.firm_id', $selectedFirmId)->select(['a.*', 'p.agent_name as parent_name'])->orderBy('a.agent_name')->get();
            $activations = DB::table('recruiter_activation_events as e')->join('recruiter_agents as a', 'e.agent_id', '=', 'a.id')->leftJoin('facilities as f', 'e.facility_id', '=', 'f.id')->where('e.firm_id', $selectedFirmId)->select(['e.*', 'a.agent_name', 'f.facility_name'])->orderByDesc('e.created_at')->limit(50)->get();
            $ledger = DB::table('recruiter_ledger_entries')->where('firm_id', $selectedFirmId)->orderByDesc('created_at')->limit(50)->get();
        }

        return view('admin.recruiter', compact('currency', 'firms', 'agentCounts', 'activationCounts', 'commissionTotals', 'selectedFirm', 'selectedFirmId', 'agents', 'activations', 'ledger'));
    }
}
