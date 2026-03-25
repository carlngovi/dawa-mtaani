<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SuperDesignFeeController
 *
 * super_admin, design fee tranche release
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SuperDesignFeeController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Design Fee',
            'portalSubtitle' => 'Design fee tranche release and disbursement management.',
        ]);
    }
    public function release(Request $request, $tranche)
    {
        return response()->json(["status" => "stub"]);
    }
}
