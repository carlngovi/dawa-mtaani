<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FinanceCreditController
 *
 * shared_accountant, credit positions view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinanceCreditController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Credit Positions',
            'portalSubtitle' => 'Live credit positions across all active facilities.',
        ]);
    }
}
