<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
class RetailPosController extends Controller
{
    public function index() { return view('retail.pos'); }
}
