<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = DB::table('system_settings')
            ->orderBy('key')
            ->get();
        return view('admin.settings', compact('settings'));
    }
}
