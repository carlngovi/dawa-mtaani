<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
class AdminPaymentsController extends Controller
{
    public function index() { return view('admin.payments'); }
}
