<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * RepPharmaciesController
 *
 * sales_rep, county pharmacy read-only list
 * Controller is a stub — business logic to be wired by Datanav.
 */
class RepPharmaciesController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Pharmacy List',
            'portalSubtitle' => 'Read-only county pharmacy list and activation status.',
        ]);
    }
    public function show(Request $request, string $ulid = "")
    {
        return response()->json(["status" => "stub"]);
    }
}
