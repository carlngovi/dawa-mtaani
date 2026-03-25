<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

class AdminDpaController extends Controller
{
    public function index() { return view('admin.dpa'); }
}
