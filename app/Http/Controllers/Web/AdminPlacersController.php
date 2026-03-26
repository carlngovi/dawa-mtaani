<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminPlacersController
 *
 * Admin Tier 2+3, authorised placer management
 * Controller is a stub — business logic to be wired by Datanav.
 */
class AdminPlacersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['network_admin', 'admin', 'super_admin', 'technical_admin', 'assistant_admin'])) {
            return redirect('/dashboard');
        }

        $placers = DB::table('facility_authorised_placers as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->join('facilities as f', 'p.facility_id', '=', 'f.id')
            ->select([
                'p.id', 'p.is_active', 'p.added_at',
                'u.name as user_name', 'u.email',
                'f.facility_name', 'f.ulid as facility_ulid', 'f.county',
            ])
            ->when($request->filled('active'), fn($q) => $q->where('p.is_active', $request->active === '1'))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('u.name', 'like', '%' . $request->search . '%')
                  ->orWhere('u.email', 'like', '%' . $request->search . '%')
                  ->orWhere('f.facility_name', 'like', '%' . $request->search . '%');
            }))
            ->orderBy('p.added_at', 'desc')
            ->paginate(25)->withQueryString();

        $stats = [
            'total'    => DB::table('facility_authorised_placers')->count(),
            'active'   => DB::table('facility_authorised_placers')->where('is_active', true)->count(),
            'inactive' => DB::table('facility_authorised_placers')->where('is_active', false)->count(),
        ];

        return view('admin.placers', compact('placers', 'stats'));
    }
}
