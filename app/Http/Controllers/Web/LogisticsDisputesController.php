<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * LogisticsDisputesController
 *
 * logistics_facility, dispute inbox
 * Controller is a stub — business logic to be wired by Datanav.
 */
class LogisticsDisputesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Disputes',
            'portalSubtitle' => 'Delivery disputes raised against SGA.',
        ]);
    }
}
