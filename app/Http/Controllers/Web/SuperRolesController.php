<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SuperRolesController
 *
 * super_admin, role assignment management
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SuperRolesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Role Management',
            'portalSubtitle' => 'Assign and revoke roles for all platform users.',
        ]);
    }
}
