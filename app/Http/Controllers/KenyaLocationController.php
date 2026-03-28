<?php

namespace App\Http\Controllers;

use App\Models\KenyaConstituency;
use App\Models\KenyaCounty;
use Illuminate\Http\JsonResponse;

class KenyaLocationController extends Controller
{
    public function counties(): JsonResponse
    {
        $counties = KenyaCounty::orderBy('county_name')
            ->get(['id', 'county_name as name']);

        return response()->json($counties);
    }

    public function constituencies(KenyaCounty $county): JsonResponse
    {
        $constituencies = $county->constituencies()
            ->orderBy('constituency_name')
            ->get(['id', 'constituency_name as name']);

        return response()->json($constituencies);
    }

    public function wards(KenyaConstituency $constituency): JsonResponse
    {
        $wards = $constituency->wards()
            ->orderBy('ward_name')
            ->get(['id', 'ward_name as name']);

        return response()->json($wards);
    }
}
