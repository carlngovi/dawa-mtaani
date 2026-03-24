<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminFacilitiesController extends Controller
{
    public function index(Request $request)
    {
        return view(strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace('Controller', '', 'AdminFacilitiesController'))));
    }

    public function show(Request $request, string $ulid = '')
    {
        return view(strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace('Controller', '', 'AdminFacilitiesController'))) . '-show');
    }
}
