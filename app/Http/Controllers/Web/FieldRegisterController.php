<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldRegisterController extends Controller
{
    public function index()
    {
        $counties = collect();
        if (Schema::hasTable('kenya_counties')) {
            $counties = DB::table('kenya_counties')->orderBy('name')->pluck('name');
        }
        return view('field.register', compact('counties'));
    }
}
