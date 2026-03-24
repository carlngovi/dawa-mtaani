<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    public function index()
    {
        $exports = DB::table('reporting_exports as re')
            ->leftJoin('users as u', 're.exported_by', '=', 'u.id')
            ->select(['re.*', 'u.name as exported_by_name'])
            ->orderBy('re.created_at', 'desc')
            ->paginate(20);

        return view('admin.reports', compact('exports'));
    }
}
