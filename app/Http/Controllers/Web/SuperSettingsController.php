<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SuperSettingsController
 *
 * super_admin, system settings management
 * Controller is a stub — business logic to be wired by Datanav.
 */
class SuperSettingsController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'System Settings',
            'portalSubtitle' => 'Platform-wide configuration — currency, thresholds, integrations.',
        ]);
    }
    public function update(Request $request)
    {
        return response()->json(["status" => "stub"]);
    }
}
