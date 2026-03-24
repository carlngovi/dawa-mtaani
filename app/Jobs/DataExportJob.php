<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataExportJob extends MonitoredJob
{
    public function __construct(
        private readonly int $exportRequestId,
        private readonly string $exportType = 'facility'
    ) {}

    public function execute(): void
    {
        $request = DB::table('data_export_requests')
            ->where('id', $this->exportRequestId)
            ->first();

        if (! $request) {
            Log::error('DataExportJob: export request not found', [
                'id' => $this->exportRequestId,
            ]);
            return;
        }

        DB::table('data_export_requests')
            ->where('id', $this->exportRequestId)
            ->update(['status' => 'GENERATING']);

        try {
            $exportData = $this->gatherExportData($request->facility_id);

            $fileName = 'exports/' . $request->ulid . '_' . now()->timestamp . '.json';

            Storage::put($fileName, json_encode($exportData, JSON_PRETTY_PRINT));

            $expiresAt = Carbon::now('UTC')->addHours(24);
            $downloadUrl = Storage::temporaryUrl($fileName, $expiresAt);

            DB::table('data_export_requests')
                ->where('id', $this->exportRequestId)
                ->update([
                    'status'               => 'READY',
                    'file_path'            => $fileName,
                    'download_url'         => $downloadUrl,
                    'download_expires_at'  => $expiresAt,
                ]);

            Log::info('DataExportJob: export ready', [
                'request_id'  => $this->exportRequestId,
                'facility_id' => $request->facility_id,
                'file'        => $fileName,
            ]);

        } catch (\Throwable $e) {
            Log::error('DataExportJob: failed', [
                'request_id' => $this->exportRequestId,
                'error'      => $e->getMessage(),
            ]);

            DB::table('data_export_requests')
                ->where('id', $this->exportRequestId)
                ->update(['status' => 'REJECTED']);

            throw $e;
        }

        $this->completed();
    }

    private function gatherExportData(int $facilityId): array
    {
        $data = [];

        // Facility profile
        try {
            $data['facility_profile'] = DB::table('facilities')
                ->where('id', $facilityId)
                ->first();
        } catch (\Throwable) {
            $data['facility_profile'] = null;
        }

        // Order history — basic fields only
        try {
            $data['order_history'] = DB::table('orders')
                ->where('retail_facility_id', $facilityId)
                ->select(['id', 'ulid', 'status', 'total_amount', 'created_at'])
                ->get();
        } catch (\Throwable) {
            $data['order_history'] = [];
        }

        // POS dispensing entries
        try {
            $data['pos_entries'] = DB::table('dispensing_entries')
                ->where('facility_id', $facilityId)
                ->select(['id', 'product_id', 'quantity', 'selling_price', 'dispensed_at'])
                ->get();
        } catch (\Throwable) {
            $data['pos_entries'] = [];
        }

        // Quality flags submitted
        try {
            $data['quality_flags'] = DB::table('quality_flags')
                ->where('facility_id', $facilityId)
                ->select(['id', 'product_id', 'flag_type', 'status', 'created_at'])
                ->get();
        } catch (\Throwable) {
            $data['quality_flags'] = [];
        }

        $data['exported_at'] = Carbon::now('UTC')->toISOString();
        $data['export_note'] = 'This export contains personal data held by Dawa Mtaani ' .
                               'for your facility under the Kenya Data Protection Act 2019.';

        return $data;
    }
}
