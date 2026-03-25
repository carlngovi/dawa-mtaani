<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * RepSummaryController
 *
 * sales_rep, county activation summary
 * Controller is a stub — business logic to be wired by Datanav.
 */
class RepSummaryController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Activation Summary',
            'portalSubtitle' => 'County-level activation and onboarding progress.',
        ]);
    }
}
