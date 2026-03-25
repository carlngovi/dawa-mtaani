<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TechWriteController
 *
 * technical_admin, gated write tool
 * Controller is a stub — business logic to be wired by Datanav.
 */
class TechWriteController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Gated Write',
            'portalSubtitle' => 'Incident-response write tool. Every operation requires Tier 1 sign-off.',
        ]);
    }
}
