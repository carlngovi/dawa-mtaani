<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FinanceSweepsController
 *
 * shared_accountant, fund sweeps view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceSweepsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Fund Sweeps',
            'portalSubtitle' => 'Platform fund sweep records and reconciliation.',
        ]);
    }
}
