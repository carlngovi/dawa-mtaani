<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FieldPharmaciesController
 *
 * network_field_agent, county pharmacy list
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FieldPharmaciesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'My Pharmacies',
            'portalSubtitle' => 'County pharmacy list — registrations, GPS, and activation status.',
        ]);
    }
}
