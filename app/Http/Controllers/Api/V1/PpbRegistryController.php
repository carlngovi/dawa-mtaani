<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\RegistryImportJob;
use App\Services\PpbVerificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PpbRegistryController extends Controller
{
    public function __construct(
        private readonly PpbVerificationService $ppbService
    ) {}

    // POST /api/v1/admin/ppb-registry/upload
    public function upload(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

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
            return response()->json([
                'message' => 'This file has already been uploaded successfully.',
            ], 422);
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

        return response()->json([
            'message'   => 'File uploaded. Import processing in background.',
            'upload_id' => $uploadId,
        ], 201);
    }

    // GET /api/v1/admin/ppb-registry/status
    public function status(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $lastUpload = DB::table('ppb_registry_uploads')
            ->where('status', 'COMPLETED')
            ->orderBy('uploaded_at', 'desc')
            ->first();

        $cacheCount = DB::table('ppb_registry_cache')->count();
        $isStale = $this->ppbService->isRegistryStale();

        return response()->json([
            'last_uploaded_at'  => $lastUpload?->uploaded_at,
            'last_row_count'    => $lastUpload?->row_count,
            'cache_record_count' => $cacheCount,
            'is_stale'          => $isStale,
            'stale_warning'     => $isStale
                ? 'PPB registry data is stale. Please upload a fresh export.'
                : null,
        ]);
    }

    // GET /api/v1/admin/ppb-registry/uploads
    public function uploads(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $uploads = DB::table('ppb_registry_uploads')
            ->orderBy('uploaded_at', 'desc')
            ->paginate(20);

        return response()->json($uploads);
    }

    // GET /api/v1/admin/ppb-registry/search
    public function search(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'licence' => 'required|string|max:100',
        ]);

        $record = DB::table('ppb_registry_cache')
            ->where('licence_number', $request->licence)
            ->first();

        if (! $record) {
            return response()->json([
                'found'   => false,
                'message' => 'Licence number not found in current PPB registry.',
            ]);
        }

        return response()->json([
            'found'  => true,
            'record' => $record,
        ]);
    }
}
