<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * GroupPlacersController
 *
 * group_owner, authorised placer management per outlet
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupPlacersController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $group = DB::table('pharmacy_groups')
            ->where('group_owner_user_id', $user->id)
            ->first();

        if (! $group) return redirect('/group/dashboard');

        $outletIds = DB::table('pharmacy_group_members')
            ->where('group_id', $group->id)
            ->pluck('facility_id');

        $placers = DB::table('facility_authorised_placers as p')
            ->join('users as u',      'p.user_id',     '=', 'u.id')
            ->join('facilities as f', 'p.facility_id', '=', 'f.id')
            ->whereIn('p.facility_id', $outletIds)
            ->select(['p.id', 'p.is_active', 'p.added_at',
                      'u.name', 'u.email',
                      'f.facility_name', 'f.ulid as facility_ulid'])
            ->orderBy('f.facility_name')
            ->get();

        return view('group.placers', compact('placers', 'group'));
    }
}
