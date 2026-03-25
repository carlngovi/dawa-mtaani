<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * LogisticsInvoicesController
 *
 * logistics_facility, SGA platform invoices
 * Controller is a stub — business logic to be wired by Datanav.
 */
class LogisticsInvoicesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Invoices',
            'portalSubtitle' => 'SGA platform invoices and billing records.',
        ]);
    }
}
