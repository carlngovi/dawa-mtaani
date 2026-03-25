<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FieldRegisterController
 *
 * network_field_agent, new pharmacy registration form
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FieldRegisterController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Register Pharmacy',
            'portalSubtitle' => 'Submit a new pharmacy registration for PPB verification.',
        ]);
    }
}
