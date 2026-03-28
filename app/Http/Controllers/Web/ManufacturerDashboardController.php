<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ManufacturerDashboardController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Manufacturer Portal',
            'portalSubtitle' => 'Product registration, batch tracking, and distribution insights.',
        ]);
    }
}
