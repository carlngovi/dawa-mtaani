<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TechIncidentsController
 *
 * technical_admin, incident log
 * Controller is a stub — business logic to be wired by Datanav.
 */
class TechIncidentsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Incident Log',
            'portalSubtitle' => 'Platform incident history and resolution records.',
        ]);
    }
}
