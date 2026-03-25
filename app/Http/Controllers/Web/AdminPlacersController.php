<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * AdminPlacersController
 *
 * Admin Tier 2+3, authorised placer management
 * Controller is a stub — business logic to be wired by Datanav.
 */
class AdminPlacersController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Placer Approvals',
            'portalSubtitle' => 'Manage authorised order placers per facility.',
        ]);
    }
}
