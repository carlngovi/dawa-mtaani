<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FinanceSettlementController
 *
 * shared_accountant, settlement records view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceSettlementController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Settlement Records',
            'portalSubtitle' => 'NILA weekly settlement records and fund sweeps.',
        ]);
    }
}
