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

class SuperRolesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['super_admin', 'technical_admin'])) {
            return redirect('/dashboard');
        }

        $users = DB::table('users as u')
            ->leftJoin('model_has_roles as mr', function ($join) {
                $join->on('u.id', '=', 'mr.model_id')
                     ->where('mr.model_type', '=', 'App\\Models\\User');
            })
            ->leftJoin('roles as r', 'mr.role_id', '=', 'r.id')
            ->when($request->filled('role'),   fn($q) => $q->where('r.name', $request->role))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('u.name',  'like', '%' . $request->search . '%')
                  ->orWhere('u.email', 'like', '%' . $request->search . '%');
            }))
            ->select(['u.id', 'u.name', 'u.email', 'u.county', 'r.name as role_name'])
            ->orderBy('r.name')
            ->orderBy('u.name')
            ->paginate(30)->withQueryString();

        $allRoles = DB::table('roles')->orderBy('name')->pluck('name');

        $roleCounts = DB::table('model_has_roles as mr')
            ->join('roles as r', 'mr.role_id', '=', 'r.id')
            ->where('mr.model_type', 'App\\Models\\User')
            ->selectRaw('r.name as role_name, COUNT(*) as cnt')
            ->groupBy('r.name')
            ->pluck('cnt', 'role_name');

        return view('super.roles', compact('users', 'allRoles', 'roleCounts'));
    }
}
