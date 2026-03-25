<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * WholesaleSettlementController
 *
 * wholesale_facility, NILA settlement view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleSettlementController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Settlement',
            'portalSubtitle' => 'View NILA weekly settlement records and payment status.',
        ]);
    }
}
