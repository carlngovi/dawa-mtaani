<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GroupOrderController
 *
 * group_owner, place group order
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupOrderController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Place Group Order',
            'portalSubtitle' => 'Place an order on behalf of a member outlet.',
        ]);
    }
}
