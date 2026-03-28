<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

/**
 * KenyaGeographySeeder
 *
 * Seeds kenya_counties, kenya_constituencies, and kenya_wards from the
 * IEBC 2022 General Election administrative units CSV.
 *
 * Source: Independent Electoral and Boundaries Commission (IEBC)
 * File: database/seeders/data/Kenya_IEBC_Administrative_Units.csv
 *
 * CSV format (after 3 header rows):
 * County Code, County Name, Constituency Code, Constituency Name,
 * Ward Code, Ward Name, Registered Voters
 */
class KenyaGeographySeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/Kenya_IEBC_Administrative_Units.csv');

        if (! file_exists($file)) {
            $this->command->error('Missing: database/seeders/data/Kenya_IEBC_Administrative_Units.csv');
            $this->command->warn('Copy the IEBC CSV file into database/seeders/data/ and re-run.');
            return;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(2); // Row 3 is the header (0-indexed = 2)

        $counties        = [];
        $constituencies  = [];
        $wards           = [];

        $now = now();

        foreach ($csv->getRecords() as $record) {
            $countyCode          = (int) trim($record['County Code'] ?? '');
            $countyName          = strtoupper(trim($record['County Name'] ?? ''));
            $constituencyCode    = (int) trim($record['Constituency Code'] ?? '');
            $constituencyName    = trim($record['Constituency Name'] ?? '');
            $wardCode            = (int) trim($record['Ward Code'] ?? '');
            $wardName            = trim($record['Ward Name'] ?? '');

            if (! $countyCode || ! $countyName) continue;

            // Collect unique counties
            if (! isset($counties[$countyCode])) {
                $counties[$countyCode] = [
                    'county_code' => $countyCode,
                    'county_name' => $countyName,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            // Collect unique constituencies
            if ($constituencyCode && ! isset($constituencies[$constituencyCode])) {
                $constituencies[$constituencyCode] = [
                    'constituency_code' => $constituencyCode,
                    'constituency_name' => $constituencyName,
                    'county_code'       => $countyCode,
                ];
            }

            // All wards
            if ($wardCode && $wardName) {
                $wards[$wardCode] = [
                    'ward_code'         => $wardCode,
                    'ward_name'         => $wardName,
                    'constituency_code' => $constituencyCode,
                    'county_code'       => $countyCode,
                ];
            }
        }

        // Insert counties
        DB::table('kenya_counties')->upsert(
            array_values($counties),
            ['county_code'],
            ['county_name', 'updated_at']
        );
        $this->command->info('Counties seeded: ' . count($counties));

        // Build county_code -> id map
        $countyIdMap = DB::table('kenya_counties')
            ->pluck('id', 'county_code')
            ->toArray();

        // Insert constituencies with foreign key
        $constRecords = [];
        foreach ($constituencies as $c) {
            $countyId = $countyIdMap[$c['county_code']] ?? null;
            if (! $countyId) continue;

            $constRecords[] = [
                'constituency_code' => $c['constituency_code'],
                'constituency_name' => $c['constituency_name'],
                'kenya_county_id'   => $countyId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('kenya_constituencies')->upsert(
            $constRecords,
            ['constituency_code'],
            ['constituency_name', 'updated_at']
        );
        $this->command->info('Constituencies seeded: ' . count($constRecords));

        // Build constituency_code -> id map
        $constIdMap = DB::table('kenya_constituencies')
            ->pluck('id', 'constituency_code')
            ->toArray();

        // Insert wards with foreign keys
        $wardRecords = [];
        foreach ($wards as $w) {
            $countyId = $countyIdMap[$w['county_code']] ?? null;
            $constId  = $constIdMap[$w['constituency_code']] ?? null;
            if (! $countyId || ! $constId) continue;

            $wardRecords[] = [
                'ward_code'              => $w['ward_code'],
                'ward_name'              => $w['ward_name'],
                'kenya_constituency_id'  => $constId,
                'kenya_county_id'        => $countyId,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        $wardChunks = array_chunk($wardRecords, 200);
        foreach ($wardChunks as $chunk) {
            DB::table('kenya_wards')->upsert(
                $chunk,
                ['ward_code'],
                ['ward_name', 'updated_at']
            );
        }
        $this->command->info('Wards seeded: ' . count($wardRecords));
    }
}
