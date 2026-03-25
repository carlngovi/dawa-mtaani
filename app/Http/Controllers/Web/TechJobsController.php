<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TechJobsController
 *
 * technical_admin, job monitor
 * Controller is a stub — business logic to be wired by Datanav.
 */
class TechJobsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Job Monitor',
            'portalSubtitle' => 'Background job health, heartbeats, and failure alerts.',
        ]);
    }
}
