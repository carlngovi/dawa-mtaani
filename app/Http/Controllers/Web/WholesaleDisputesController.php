<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * WholesaleDisputesController
 *
 * wholesale_facility, dispute inbox
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleDisputesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Disputes',
            'portalSubtitle' => 'Manage delivery disputes raised against your facility.',
        ]);
    }
}
