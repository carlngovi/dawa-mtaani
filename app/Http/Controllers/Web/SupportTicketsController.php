<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SupportTicketsController
 *
 * admin_support, read-only support ticket list
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SupportTicketsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Support Tickets',
            'portalSubtitle' => 'Read-only support queue — facility and order queries.',
        ]);
    }
}
