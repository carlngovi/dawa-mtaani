<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFacilitiesController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('facilities')
            ->whereNull('deleted_at')
            ->orderBy('facility_name');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('facility_name', 'like', $search)
                  ->orWhere('ppb_licence_number', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }

        if ($request->filled('status'))      $query->where('facility_status', $request->status);
        if ($request->filled('type'))        $query->where('ppb_facility_type', $request->type);
        if ($request->filled('county'))      $query->where('county', $request->county);
        if ($request->filled('membership'))  $query->where('network_membership', $request->membership);
        if ($request->filled('gps_pending')) $query->whereNull('latitude');

        $facilities = $query->paginate(30)->withQueryString();

        $counties = DB::table('facilities')
            ->whereNull('deleted_at')
            ->distinct()->orderBy('county')->pluck('county');

        $stats = [
            'total'       => DB::table('facilities')->whereNull('deleted_at')->count(),
            'active'      => DB::table('facilities')->where('facility_status', 'ACTIVE')->count(),
            'network'     => DB::table('facilities')->where('network_membership', 'NETWORK')->count(),
            'gps_pending' => DB::table('facilities')->whereNull('latitude')->where('facility_status', 'ACTIVE')->count(),
        ];

        return view('admin.facilities', compact('facilities', 'counties', 'stats'));
    }

    public function show(Request $request, string $ulid)
    {
        $facility = DB::table('facilities')->where('ulid', $ulid)->first();

        if (! $facility) abort(404);

        $recentOrders = DB::table('orders')
            ->where('retail_facility_id', $facility->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $flags = DB::table('facility_flags')
            ->where('facility_id', $facility->id)
            ->whereNull('resolved_at')
            ->get();

        $ppbLogs = DB::table('ppb_verification_logs')
            ->where('facility_id', $facility->id)
            ->orderBy('checked_at', 'desc')
            ->limit(5)
            ->get();

        $authorisedPlacers = DB::table('facility_authorised_placers as fap')
            ->join('users as u', 'fap.user_id', '=', 'u.id')
            ->where('fap.facility_id', $facility->id)
            ->where('fap.is_active', true)
            ->select(['u.name', 'u.email', 'u.phone', 'fap.added_at'])
            ->get();

        return view('admin.facility-show', compact(
            'facility', 'recentOrders', 'flags', 'ppbLogs', 'authorisedPlacers'
        ));
    }
}
