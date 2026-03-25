<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminNotificationsController extends Controller
{
    public function index()
    {
        $notifications = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->paginate(30);
        return view('admin.notifications', compact('notifications'));
    }
}
