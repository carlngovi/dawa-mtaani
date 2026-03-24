<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegistryImportJob extends MonitoredJob
{
    public function __construct(
        private readonly int $uploadId,
        private readonly string $filePath,
        private readonly int $uploadedBy
    ) {}

    public function execute(): void
    {
        $now = Carbon::now('UTC');
        $inserted = 0;
        $updated = 0;
        $rejected = 0;
        $errors = [];

        try {
            // Get column mapping from system_settings
            $columnMap = json_decode(
                DB::table('system_settings')
                    ->where('key', 'ppb_csv_column_map')
                    ->value('value') ?? '{}',
                true
            );

            $fileContents = Storage::get($this->filePath);

            if (! $fileContents) {
                throw new \RuntimeException('File not found: ' . $this->filePath);
            }

            $lines = explode("\n", trim($fileContents));
            $totalRows = count($lines);

            // Skip header row if first line contains text headers
            $startRow = 0;
            if (isset($lines[0]) && ! is_numeric(explode(',', $lines[0])[0] ?? '')) {
                $startRow = 1;
            }

            for ($i = $startRow; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                $lineNumber = $i + 1;

                try {
                    $columns = str_getcsv($line);

                    $licenceNumber   = trim($columns[$columnMap['licence_number'] ?? 0] ?? '');
                    $facilityName    = trim($columns[$columnMap['facility_name'] ?? 1] ?? '');
                    $ppbType         = strtoupper(trim($columns[$columnMap['ppb_type'] ?? 2] ?? ''));
                    $licenceStatus   = strtoupper(trim($columns[$columnMap['licence_status'] ?? 3] ?? ''));
                    $registeredAddr  = trim($columns[$columnMap['registered_address'] ?? 4] ?? '');
                    $expiryDate      = trim($columns[$columnMap['licence_expiry_date'] ?? 5] ?? '');

                    // Validate required fields
                    if (empty($licenceNumber)) {
                        throw new \InvalidArgumentException('licence_number is required');
                    }

                    if (! in_array($ppbType, ['RETAIL', 'WHOLESALE', 'HOSPITAL', 'MANUFACTURER'])) {
                        throw new \InvalidArgumentException("Invalid ppb_type: {$ppbType}");
                    }

                    if (! in_array($licenceStatus, ['VALID', 'EXPIRED', 'SUSPENDED'])) {
                        throw new \InvalidArgumentException("Invalid licence_status: {$licenceStatus}");
                    }

                    $expiryDateParsed = null;
                    if (! empty($expiryDate)) {
                        try {
                            $expiryDateParsed = Carbon::parse($expiryDate)->toDateString();
                        } catch (\Throwable) {
                            // Invalid date — store null
                        }
                    }

                    $existing = DB::table('ppb_registry_cache')
                        ->where('licence_number', $licenceNumber)
                        ->first();

                    if ($existing) {
                        DB::table('ppb_registry_cache')
                            ->where('licence_number', $licenceNumber)
                            ->update([
                                'facility_name'       => $facilityName,
                                'ppb_type'            => $ppbType,
                                'licence_status'      => $licenceStatus,
                                'registered_address'  => $registeredAddr ?: null,
                                'licence_expiry_date' => $expiryDateParsed,
                                'last_uploaded_at'    => $now,
                                'upload_batch_id'     => $this->uploadId,
                                'updated_at'          => $now,
                            ]);
                        $updated++;
                    } else {
                        DB::table('ppb_registry_cache')->insert([
                            'licence_number'      => $licenceNumber,
                            'facility_name'       => $facilityName,
                            'ppb_type'            => $ppbType,
                            'licence_status'      => $licenceStatus,
                            'registered_address'  => $registeredAddr ?: null,
                            'licence_expiry_date' => $expiryDateParsed,
                            'last_uploaded_at'    => $now,
                            'upload_batch_id'     => $this->uploadId,
                            'created_at'          => $now,
                            'updated_at'          => $now,
                        ]);
                        $inserted++;
                    }

                } catch (\Throwable $e) {
                    $rejected++;
                    $errors[] = [
                        'line'   => $lineNumber,
                        'error'  => $e->getMessage(),
                        'data'   => $line,
                    ];
                }
            }

            // Update upload record with results
            DB::table('ppb_registry_uploads')
                ->where('id', $this->uploadId)
                ->update([
                    'status'        => 'COMPLETED',
                    'row_count'     => $totalRows - ($startRow),
                    'rows_inserted' => $inserted,
                    'rows_updated'  => $updated,
                    'rows_rejected' => $rejected,
                    'error_report'  => ! empty($errors) ? json_encode($errors) : null,
                ]);

        } catch (\Throwable $e) {
            DB::table('ppb_registry_uploads')
                ->where('id', $this->uploadId)
                ->update([
                    'status'       => 'FAILED',
                    'error_report' => json_encode([['error' => $e->getMessage()]]),
                ]);

            Log::error('RegistryImportJob: import failed', [
                'upload_id' => $this->uploadId,
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->completed($inserted + $updated);
    }
}
