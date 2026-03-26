<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CreditTranche;
use App\Models\CreditTier;
use App\Models\CreditTrancheParty;
use App\Models\CreditProgressionRule;
use App\Models\CreditConfigChangelog;
use App\Models\FacilityCreditAccount;
use App\Models\FacilityTrancheBalance;
use App\Services\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminCreditController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['network_admin', 'admin', 'super_admin', 'technical_admin', 'assistant_admin', 'shared_accountant'])) abort(403);

        $currency = CurrencyConfig::get();
        $tranches = CreditTranche::with(['activeParties', 'activeTiers'])->orderBy('id')->get();
        $progressionRules = CreditProgressionRule::orderBy('sort_order')->get();
        $changelog = CreditConfigChangelog::with('changedByUser')->latest('changed_at')->limit(50)->get();
        $totalActive = FacilityCreditAccount::where('account_status', 'ACTIVE')->count();
        $totalSuspended = FacilityCreditAccount::where('account_status', 'SUSPENDED')->count();
        $totalPending = FacilityCreditAccount::where('account_status', 'PENDING_ASSESSMENT')->count();

        return view('admin.credit', compact('currency', 'tranches', 'progressionRules', 'changelog', 'totalActive', 'totalSuspended', 'totalPending'));
    }

    public function storeTranche(Request $request)
    {
        if (! Auth::user()->hasAnyRole(['network_admin', 'admin', 'super_admin'])) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:100', 'entry_amount' => 'required|numeric|min:0',
            'ceiling_amount' => 'nullable|numeric|min:0', 'is_fixed' => 'boolean',
            'approval_pathway' => 'required|in:AUTOMATIC,ASSESSED',
            'product_restriction_scope' => 'nullable|string', 'effective_from' => 'required|date',
        ]);
        $scope = null;
        if (! empty($validated['product_restriction_scope'])) {
            $scope = array_filter(array_map('trim', explode(',', $validated['product_restriction_scope'])));
        }
        CreditTranche::create([
            'ulid' => Str::ulid(), 'name' => $validated['name'],
            'entry_amount' => $validated['entry_amount'], 'ceiling_amount' => $validated['ceiling_amount'] ?? null,
            'is_fixed' => $request->boolean('is_fixed'), 'approval_pathway' => $validated['approval_pathway'],
            'product_restriction_scope' => $scope, 'effective_from' => $validated['effective_from'],
            'is_active' => true, 'created_by' => Auth::id(),
        ]);
        return back()->with('success', 'Tranche created successfully.');
    }

    public function toggleTranche(Request $request, CreditTranche $tranche)
    {
        if (! Auth::user()->hasAnyRole(['network_admin', 'admin', 'super_admin'])) abort(403);
        $tranche->update(['is_active' => ! $tranche->is_active]);
        return back()->with('success', 'Tranche ' . ($tranche->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function storeParty(Request $request, CreditTranche $tranche)
    {
        if (! Auth::user()->hasAnyRole(['network_admin', 'admin', 'super_admin'])) abort(403);
        $validated = $request->validate([
            'party_name' => 'required|string|max:255', 'party_type' => 'required|string|max:100',
            'banking_party_binding' => 'nullable|string|max:255',
            'risk_percentage' => 'required|numeric|min:0|max:100', 'return_percentage' => 'required|numeric|min:0|max:100',
        ]);
        $tranche->parties()->create($validated + ['is_active' => true]);
        return back()->with('success', 'Party added. Verify risk % sums to 100.');
    }

    public function storeTier(Request $request, CreditTranche $tranche)
    {
        if (! Auth::user()->hasAnyRole(['network_admin', 'admin', 'super_admin'])) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:100', 'product_scope_description' => 'required|string',
            'unlock_threshold_pct' => 'required|numeric|min:0|max:100',
            'allocation_pct' => 'required|numeric|min:0|max:100',
            'approval_required' => 'boolean', 'sort_order' => 'integer|min:0',
        ]);
        $tranche->tiers()->create([
            'ulid' => Str::ulid(), 'name' => $validated['name'],
            'product_scope_description' => $validated['product_scope_description'],
            'unlock_threshold_pct' => $validated['unlock_threshold_pct'],
            'allocation_pct' => $validated['allocation_pct'],
            'approval_required' => $request->boolean('approval_required'),
            'sort_order' => $validated['sort_order'] ?? 0, 'is_active' => true,
        ]);
        return back()->with('success', 'Tier added. Verify allocation % sums to 100.');
    }

    public function storeProgressionRule(Request $request)
    {
        if (! Auth::user()->hasAnyRole(['network_admin', 'admin', 'super_admin'])) abort(403);
        $validated = $request->validate([
            'label' => 'required|string|max:100', 'max_days_to_qualify' => 'required|integer|min:1',
            'progression_rate_pct' => 'required|numeric|min:0|max:100',
            'is_suspension_trigger' => 'boolean', 'sort_order' => 'integer|min:0',
        ]);
        CreditProgressionRule::create([
            'label' => $validated['label'], 'max_days_to_qualify' => $validated['max_days_to_qualify'],
            'progression_rate_pct' => $validated['progression_rate_pct'],
            'is_suspension_trigger' => $request->boolean('is_suspension_trigger'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        return back()->with('success', 'Progression rule saved.');
    }
}
