<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SupportTicketsController
 *
 * admin_support, read-only support ticket list
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportTicketsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['admin_support', 'admin', 'super_admin', 'assistant_admin'])) {
            return redirect('/dashboard');
        }

        $tickets = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $stats   = ['open' => 0, 'resolved' => 0];

        try {
            if (Schema::hasTable('support_tickets')) {
                $tickets = DB::table('support_tickets')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

                $stats = [
                    'open'     => DB::table('support_tickets')->where('status', 'OPEN')->count(),
                    'resolved' => DB::table('support_tickets')->where('status', 'RESOLVED')->count(),
                ];
            }
        } catch (\Exception $e) {
            // Table structure unknown — leave defaults
        }

        return view('support.tickets', compact('tickets', 'stats'));
    }
}
