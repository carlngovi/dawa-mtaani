<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GroupOrderHistoryController
 *
 * group_owner, order history across member outlets
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupOrderHistoryController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Order History',
            'portalSubtitle' => 'Full order history across all group outlets.',
        ]);
    }
}
