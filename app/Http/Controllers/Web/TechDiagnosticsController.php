<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * TechDiagnosticsController
 *
 * technical_admin, diagnostics and gated SQL query
 * Controller is a stub — business logic to be wired by Datanav.
 */
class TechDiagnosticsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Diagnostics',
            'portalSubtitle' => 'System diagnostics — read-only. Gated write requires super_admin confirmation.',
        ]);
    }
    public function query(Request $request)
    {
        return response()->json(["status" => "stub"]);
    }
}
