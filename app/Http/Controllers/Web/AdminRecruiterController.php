<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;

class AdminRecruiterController extends Controller
{
    public function index() { return view('admin.recruiter'); }
}
