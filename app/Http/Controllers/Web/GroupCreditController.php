<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GroupCreditController
 *
 * group_owner, consolidated credit view (read-only)
 * Controller is a stub — business logic to be wired by Datanav.
 */
class GroupCreditController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Consolidated Credit',
            'portalSubtitle' => 'Read-only credit positions across all member outlets.',
        ]);
    }
}
