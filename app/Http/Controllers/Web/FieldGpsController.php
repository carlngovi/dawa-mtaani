<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldGpsController extends Controller
{
    public function index(Request $request, $ulid = null)
    {
        $county   = Auth::user()->county;
        $facility = null;

        if ($ulid) {
            $facility = DB::table('facilities')->where('ulid', $ulid)->first();
        }

        $pending = DB::table('facilities')
            ->where('county', $county)
            ->whereNull('latitude')
            ->whereNull('deleted_at')
            ->select(['ulid', 'facility_name', 'ward'])
            ->orderBy('facility_name')
            ->get();

        return view('field.gps', compact('facility', 'pending', 'county'));
    }
}
