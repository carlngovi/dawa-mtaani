<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogisticsInvoicesController extends Controller
{
    public function index()
    {
        $currency = CurrencyConfig::get();
        $invoices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        if (Schema::hasTable('logistics_invoices')) {
            $invoices = DB::table('logistics_invoices')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('logistics.invoices', compact('invoices', 'currency'));
    }
}
