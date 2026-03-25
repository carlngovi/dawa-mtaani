<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * AssistantDashboardController
 *
 * assistant_admin, operational dashboard
 * Controller is a stub — business logic to be wired by Datanav.
 */
class AssistantDashboardController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Assistant Dashboard',
            'portalSubtitle' => 'Operational actions — registration approvals, disputes, content.',
        ]);
    }
}
