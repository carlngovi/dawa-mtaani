<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FieldGpsController
 *
 * network_field_agent, GPS capture tool
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FieldGpsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'GPS Capture',
            'portalSubtitle' => 'Capture or update GPS coordinates for a facility.',
        ]);
    }
}
