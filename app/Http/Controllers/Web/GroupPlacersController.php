<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GroupPlacersController
 *
 * group_owner, authorised placer management per outlet
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupPlacersController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Placer Management',
            'portalSubtitle' => 'Manage authorised order placers per member outlet.',
        ]);
    }
}
