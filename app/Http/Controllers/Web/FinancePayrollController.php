<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * FinancePayrollController
 *
 * shared_accountant, payroll view
 * Controller is a stub — business logic to be wired by Datanav.
 */
class FinancePayrollController extends Controller
{
    public function index()
    {
        return view('placeholder', [
            'portalTitle'    => 'Payroll View',
            'portalSubtitle' => 'Read-only payroll and recruiter commission records.',
        ]);
    }
}
