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

class TechIncidentsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['technical_admin', 'super_admin'])) {
            return redirect('/dashboard');
        }
        $incidents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        if (Schema::hasTable('security_events')) {
            $incidents = DB::table('security_events')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        return view('tech.incidents', compact('incidents'));
    }
}
