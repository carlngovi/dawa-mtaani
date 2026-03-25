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
            $registeredVoters    = (int) trim($record['Registered Voters'] ?? 0);

            if (! $countyCode || ! $countyName) continue;

            // Collect unique counties
            if (! isset($counties[$countyCode])) {
                $counties[$countyCode] = [
                    'code'       => $countyCode,
                    'name'       => $countyName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Collect unique constituencies
            if ($constituencyCode && ! isset($constituencies[$constituencyCode])) {
                $constituencies[$constituencyCode] = [
                    'code'        => $constituencyCode,
                    'county_code' => $countyCode,
                    'name'        => $constituencyName,
                ];
            }

            // All wards (duplicates possible if ward code reused across constituencies)
            if ($wardCode && $wardName) {
                $wards[] = [
                    'constituency_code' => $constituencyCode,
                    'county_code'       => $countyCode,
                    'name'              => $wardName,
                    'registered_voters' => $registeredVoters,
                ];
            }
        }

        // Insert counties
        DB::table('kenya_counties')->upsert(
            array_values($counties),
            ['code'],
            ['name', 'updated_at']
        );
        $this->command->info('Counties seeded: ' . count($counties));

        // Insert constituencies
        DB::table('kenya_constituencies')->upsert(
            array_values($constituencies),
            ['code'],
            ['name']
        );
        $this->command->info('Constituencies seeded: ' . count($constituencies));

        // Insert wards in chunks (large dataset)
        $wardChunks = array_chunk($wards, 200);
        foreach ($wardChunks as $chunk) {
            DB::table('kenya_wards')->insert($chunk);
        }
        $this->command->info('Wards seeded: ' . count($wards));
    }
}
