<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FieldDisputesController
 *
 * network_field_agent, dispute verification
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FieldDisputesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Dispute Verification',
            'portalSubtitle' => 'Field-verify delivery disputes in your county.',
        ]);
    }
}
