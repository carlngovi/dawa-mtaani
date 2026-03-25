<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SupportOrdersController
 *
 * admin_support, read-only order lookup
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportOrdersController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Order Lookup',
            'portalSubtitle' => 'Read-only order search and status viewer.',
        ]);
    }
}
