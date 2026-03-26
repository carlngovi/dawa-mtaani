<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepPharmaciesController extends Controller
{
    public function index(Request $request)
    {
        $rep    = Auth::user();
        $county = $rep->county;

        if (! $county) {
            return view('rep.pharmacies', [
                'facilities' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30),
                'county'     => null,
            ]);
        }

        $facilities = DB::table('facilities')
            ->where('county', $county)
            ->whereNull('deleted_at')
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('facility_name', 'like', '%' . $request->search . '%')
                  ->orWhere('ppb_licence',  'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('status'), fn($q) => $q->where('facility_status', $request->status))
            ->select([
                'ulid', 'facility_name', 'ppb_licence',
                'ward', 'facility_status', 'network_membership', 'created_at',
                // STRICT: no financial columns
            ])
            ->orderBy('facility_name')
            ->paginate(30)->withQueryString();

        return view('rep.pharmacies', compact('facilities', 'county'));
    }

    public function show($ulid)
    {
        $rep    = Auth::user();
        $county = $rep->county;

        // County scope enforced — rep cannot view facilities outside their county
        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->where('county', $county)
            ->whereNull('deleted_at')
            ->select([
                'ulid', 'facility_name', 'ppb_licence', 'facility_type',
                'ward', 'sub_county', 'county',
                'facility_status', 'network_membership', 'created_at',
                // STRICT: no financial, credit, or GPS columns
            ])
            ->first();

        if (! $facility) {
            abort(404);
        }

        return view('rep.pharmacies-show', compact('facility'));
    }
}
