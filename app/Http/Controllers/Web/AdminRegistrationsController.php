<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * AdminRegistrationsController
 *
 * Admin Tier 2+3, registration approval queue
 * Controller is a stub — business logic to be wired by Datanav.
 */
class AdminRegistrationsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Registration Queue',
            'portalSubtitle' => 'Review and approve incoming pharmacy registration applications.',
        ]);
    }
}
