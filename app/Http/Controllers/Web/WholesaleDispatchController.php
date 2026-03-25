<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * WholesaleDispatchController
 *
 * wholesale_facility, dispatch trigger and tracking
 * Controller is a stub — business logic to be wired by Datanav.
 */
class WholesaleDispatchController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Dispatch',
            'portalSubtitle' => 'Trigger and track order dispatches to retail facilities.',
        ]);
    }
}
