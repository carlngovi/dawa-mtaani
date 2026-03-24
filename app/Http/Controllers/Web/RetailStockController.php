<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RetailStockController extends Controller
{
    public function index(Request $request)
    {
        return view(strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace('Controller', '', 'RetailStockController'))));
    }

    public function show(Request $request, string $ulid = '')
    {
        return view(strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace('Controller', '', 'RetailStockController'))) . '-show');
    }
}
