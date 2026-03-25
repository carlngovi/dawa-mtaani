<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
class RetailStockController extends Controller
{
    public function index() { return view('retail.stock'); }
}
