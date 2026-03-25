<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SupportFacilitiesController
 *
 * admin_support, read-only facility lookup
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportFacilitiesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Facility Lookup',
            'portalSubtitle' => 'Read-only facility search and record viewer.',
        ]);
    }
}
