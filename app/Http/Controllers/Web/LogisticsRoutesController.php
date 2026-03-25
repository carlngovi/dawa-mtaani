<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * LogisticsRoutesController
 *
 * logistics_facility, routes and drivers
 * Controller is a stub — business logic to be wired by Datanav.
 */
class LogisticsRoutesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Routes & Drivers',
            'portalSubtitle' => 'Manage delivery routes and driver assignments.',
        ]);
    }
}
