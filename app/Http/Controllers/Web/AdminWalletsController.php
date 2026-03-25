<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
class AdminWalletsController extends Controller
{
    public function index() { return view('admin.wallets'); }
}
