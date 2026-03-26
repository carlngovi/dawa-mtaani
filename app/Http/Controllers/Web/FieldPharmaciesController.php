<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldPharmaciesController extends Controller
{
    public function index(Request $request)
    {
        $county = Auth::user()->county;

        if (! $county) {
            return view('field.pharmacies', [
                'facilities' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25),
                'stats'      => ['total' => 0, 'active' => 0, 'pending' => 0, 'gps_pending' => 0],
                'county'     => null,
            ]);
        }

        $facilities = DB::table('facilities')
            ->where('county', $county)
            ->whereNull('deleted_at')
            ->when($request->filled('status'), fn($q) => $q->where('facility_status', $request->status))
            ->when($request->get('gps') === 'pending',  fn($q) => $q->whereNull('latitude'))
            ->when($request->get('gps') === 'captured', fn($q) => $q->whereNotNull('latitude'))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('facility_name', 'like', '%' . $request->search . '%')
                  ->orWhere('ppb_licence',  'like', '%' . $request->search . '%');
            }))
            ->select(['id', 'ulid', 'facility_name', 'ppb_licence', 'ward',
                      'facility_status', 'network_membership', 'latitude', 'created_at'])
            ->orderBy('facility_name')
            ->paginate(25)->withQueryString();

        $stats = [
            'total'       => DB::table('facilities')->where('county', $county)->whereNull('deleted_at')->count(),
            'active'      => DB::table('facilities')->where('county', $county)->where('facility_status', 'ACTIVE')->whereNull('deleted_at')->count(),
            'pending'     => DB::table('facilities')->where('county', $county)->whereIn('facility_status', ['APPLIED', 'PPB_VERIFIED', 'ACCOUNT_LINKED'])->whereNull('deleted_at')->count(),
            'gps_pending' => DB::table('facilities')->where('county', $county)->whereNull('latitude')->whereNull('deleted_at')->count(),
        ];

        return view('field.pharmacies', compact('facilities', 'stats', 'county'));
    }
}
