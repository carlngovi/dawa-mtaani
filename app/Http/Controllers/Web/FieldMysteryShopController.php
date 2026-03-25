<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FieldMysteryShopController
 *
 * network_field_agent, mystery shopping form
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FieldMysteryShopController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Mystery Shopping',
            'portalSubtitle' => 'Submit mystery shopping visit reports.',
        ]);
    }
}
