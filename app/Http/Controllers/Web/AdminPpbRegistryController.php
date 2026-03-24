<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Services\PpbVerificationService;
use Illuminate\Support\Facades\DB;

class AdminPpbRegistryController extends Controller
{
    public function index()
    {
        $service = app(PpbVerificationService::class);
        $isStale = $service->isRegistryStale();

        $lastUpload = DB::table('ppb_registry_uploads')
            ->where('status', 'COMPLETED')
            ->orderBy('uploaded_at', 'desc')
            ->first();

        $cacheCount = DB::table('ppb_registry_cache')->count();

        $recentUploads = DB::table('ppb_registry_uploads')
            ->orderBy('uploaded_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.ppb-registry', compact(
            'isStale', 'lastUpload', 'cacheCount', 'recentUploads'
        ));
    }
}
