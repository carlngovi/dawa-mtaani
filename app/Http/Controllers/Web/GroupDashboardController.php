<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GroupDashboardController
 *
 * group_owner, consolidated group overview
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupDashboardController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Group Dashboard',
            'portalSubtitle' => 'Consolidated view across all member pharmacy outlets.',
        ]);
    }
}
