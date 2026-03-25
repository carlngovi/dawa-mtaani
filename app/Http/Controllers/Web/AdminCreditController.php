<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AdminCreditController extends Controller
{
    public function index()
    {
        return view('admin.credit');
    }
}
