<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Http\Controllers;

class SpotterAppController extends Controller
{
    public function index()
    {
        return view('spotter.app');
    }
}
