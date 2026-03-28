<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreCounterfeitController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $reports = DB::table('customer_counterfeit_reports as r')
            ->leftJoin('facilities as f', 'r.facility_id', '=', 'f.id')
            ->leftJoin('products as p', 'r.product_id', '=', 'p.id')
            ->where('r.customer_phone', $user->phone ?? '')
            ->select([
                'r.id', 'r.report_notes', 'r.status', 'r.created_at',
                'f.facility_name',
                'p.generic_name', 'p.brand_name',
            ])
            ->orderBy('r.created_at', 'desc')
            ->paginate(15);

        return view('store.counterfeit', compact('reports'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'facility_id'  => 'required|integer|exists:facilities,id',
            'product_id'   => 'required|integer|exists:products,id',
            'report_notes' => 'required|string|min:10|max:1000',
        ]);

        DB::table('customer_counterfeit_reports')->insert([
            'facility_id'  => $request->facility_id,
            'product_id'   => $request->product_id,
            'customer_phone' => $user->phone ?? '',
            'report_notes' => $request->report_notes,
            'status'       => 'OPEN',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect('/store/report/counterfeit')->with('success', 'Report submitted successfully.');
    }
}
