<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\RegistryImportJob;
use App\Services\PpbVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:51200',
        ]);

        $file = $request->file('file');
        $fileHash = hash_file('sha256', $file->getRealPath());

        // Check for duplicate upload
        $duplicate = DB::table('ppb_registry_uploads')
            ->where('file_hash', $fileHash)
            ->where('status', 'COMPLETED')
            ->exists();

        if ($duplicate) {
            return redirect('/admin/ppb-registry')
                ->with('error', 'This file has already been uploaded successfully.');
        }

        // Store file
        $fileName = 'ppb-registry/' . now()->format('Y-m-d') . '_' . $fileHash . '.csv';
        Storage::put($fileName, file_get_contents($file->getRealPath()));

        // Create upload record
        $uploadId = DB::table('ppb_registry_uploads')->insertGetId([
            'uploaded_by' => $request->user()->id,
            'file_name'   => $file->getClientOriginalName(),
            'file_hash'   => $fileHash,
            'status'      => 'PROCESSING',
            'uploaded_at' => Carbon::now('UTC'),
        ]);

        // Dispatch import job
        RegistryImportJob::dispatch($uploadId, $fileName, $request->user()->id)
            ->onQueue('default');

        return redirect('/admin/ppb-registry')
            ->with('success', 'File uploaded. Import processing in background. Upload ID: ' . $uploadId);
    }
}
