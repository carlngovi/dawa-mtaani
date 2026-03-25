<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

/**
 * PpbRegistryCacheSeeder
 *
 * Seeds the ppb_registry_cache table from all four PPB CSV files.
 * This represents the official Pharmacy and Poisons Board registry.
 *
 * Files required in database/seeders/data/:
 *   PBB_Retail.csv       (~5,846 rows)
 *   PBB_Wholesale.csv    (~293 rows)
 *   PPB_Hospitals.csv    (~1,220 rows)
 *   PBB_Manufacturer.csv (~19 rows)
 *
 * All records use licence_status = VALID and expiry = 2026-12-31
 * as sourced from the PPB registry export.
 *
 * Uses upsert on licence_number — safe to re-run.
 */
class PpbRegistryCacheSeeder extends Seeder
{
    // Batch upload record — represents this initial seed operation
    private int $batchId;

    public function run(): void
    {
        // Create a batch record so the data is traceable
        $this->batchId = DB::table('ppb_registry_uploads')->insertGetId([
            'uploaded_by'   => 1,
            'file_name'     => 'initial_seed_batch',
            'file_hash'     => md5('seed_' . now()),
            'row_count'     => 0,
            'rows_inserted' => 0,
            'rows_updated'  => 0,
            'rows_rejected' => 0,
            'status'        => 'PROCESSING',
            'uploaded_at'   => now(),
        ]);

        $totals = ['inserted' => 0, 'rejected' => 0];

        // Process each file
        $files = [
            [
                'path'     => database_path('seeders/data/PBB_Retail.csv'),
                'type'     => 'RETAIL',
                'label'    => 'Retail',
                'has_type' => false,  // Retail CSV has no License Type column
            ],
            [
                'path'     => database_path('seeders/data/PBB_Wholesale.csv'),
                'type'     => 'WHOLESALE',
                'label'    => 'Wholesale',
                'has_type' => true,
            ],
            [
                'path'     => database_path('seeders/data/PPB_Hospitals.csv'),
                'type'     => 'HOSPITAL',
                'label'    => 'Hospitals',
                'has_type' => true,
            ],
            [
                'path'     => database_path('seeders/data/PBB_Manufacturer.csv'),
                'type'     => 'MANUFACTURER',
                'label'    => 'Manufacturers',
                'has_type' => true,
            ],
        ];

        foreach ($files as $file) {
            if (! file_exists($file['path'])) {
                $this->command->warn("Skipping {$file['label']} — file not found: {$file['path']}");
                continue;
            }

            $result = $this->processFile($file);
            $totals['inserted'] += $result['inserted'];
            $totals['rejected'] += $result['rejected'];

            $this->command->info(
                "{$file['label']}: {$result['inserted']} inserted, {$result['rejected']} rejected"
            );
        }

        // Update batch record
        DB::table('ppb_registry_uploads')->where('id', $this->batchId)->update([
            'row_count'     => $totals['inserted'] + $totals['rejected'],
            'rows_inserted' => $totals['inserted'],
            'rows_rejected' => $totals['rejected'],
            'status'        => 'COMPLETED',
        ]);

        $this->command->info('');
        $this->command->info("PPB Registry total: {$totals['inserted']} records seeded.");
    }

    private function processFile(array $file): array
    {
        $csv = Reader::createFromPath($file['path'], 'r');
        $csv->setHeaderOffset(0);

        $rows     = [];
        $rejected = 0;
        $now      = now();

        foreach ($csv->getRecords() as $record) {
            // Registration No maps to licence_number
            $licenceNumber = trim($record['Registration No'] ?? $record['License Number'] ?? '');
            $facilityName  = trim($record['Facility Name'] ?? '');

            if (! $licenceNumber || ! $facilityName) {
                $rejected++;
                continue;
            }

            // Build the registered address from Street + County
            $street  = trim($record['Street'] ?? '');
            $county  = trim($record['County'] ?? '');
            $address = implode(', ', array_filter([$street, $county]));

            // Licence expiry
            $validTill = trim($record['Valid Till'] ?? '2026-12-31');
            try {
                $expiry = \Carbon\Carbon::parse($validTill)->format('Y-m-d');
            } catch (\Throwable) {
                $expiry = '2026-12-31';
            }

            $rows[] = [
                'licence_number'     => $licenceNumber,
                'facility_name'      => $facilityName,
                'ppb_type'           => $file['type'],
                'licence_status'     => 'VALID',
                'registered_address' => $address ?: null,
                'licence_expiry_date'=> $expiry,
                'last_uploaded_at'   => $now,
                'upload_batch_id'    => $this->batchId,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        // Upsert in chunks of 500 to avoid memory issues
        $chunks = array_chunk($rows, 500);
        foreach ($chunks as $chunk) {
            DB::table('ppb_registry_cache')->upsert(
                $chunk,
                ['licence_number'],
                ['facility_name', 'ppb_type', 'licence_status',
                 'registered_address', 'licence_expiry_date',
                 'last_uploaded_at', 'upload_batch_id', 'updated_at']
            );
        }

        return ['inserted' => count($rows), 'rejected' => $rejected];
    }
}
