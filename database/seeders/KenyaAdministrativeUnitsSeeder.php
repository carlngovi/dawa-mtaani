<?php

namespace Database\Seeders;

use App\Models\KenyaConstituency;
use App\Models\KenyaCounty;
use App\Models\KenyaWard;
use Illuminate\Database\Seeder;

class KenyaAdministrativeUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/Kenya_IEBC_Administrative_Units.csv');

        if (! file_exists($path)) {
            $this->command->error("CSV not found: {$path}");
            return;
        }

        $handle = fopen($path, 'r');

        // Skip 3 header rows
        fgetcsv($handle);
        fgetcsv($handle);
        fgetcsv($handle);

        $counties = [];
        $constituencies = [];
        $wards = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6 || empty($row[0])) {
                continue;
            }

            $countyCode = (int) trim($row[0]);
            $countyName = ucwords(strtolower(trim($row[1])));
            $consCode   = (int) trim($row[2]);
            $consName   = ucwords(strtolower(trim($row[3])));
            $wardCode   = (int) trim($row[4]);
            $wardName   = ucwords(strtolower(trim($row[5])));

            if (! isset($counties[$countyCode])) {
                $counties[$countyCode] = $countyName;
            }

            if (! isset($constituencies[$consCode])) {
                $constituencies[$consCode] = [
                    'name'        => $consName,
                    'county_code' => $countyCode,
                ];
            }

            if (! isset($wards[$wardCode])) {
                $wards[$wardCode] = [
                    'name'        => $wardName,
                    'cons_code'   => $consCode,
                    'county_code' => $countyCode,
                ];
            }
        }

        fclose($handle);

        // Upsert counties
        $countyIdMap = [];
        foreach ($counties as $code => $name) {
            $county = KenyaCounty::updateOrCreate(
                ['county_code' => $code],
                ['county_name' => $name]
            );
            $countyIdMap[$code] = $county->id;
        }

        // Upsert constituencies
        $consIdMap = [];
        foreach ($constituencies as $code => $data) {
            $cons = KenyaConstituency::updateOrCreate(
                ['constituency_code' => $code],
                [
                    'constituency_name' => $data['name'],
                    'kenya_county_id'   => $countyIdMap[$data['county_code']],
                ]
            );
            $consIdMap[$code] = $cons->id;
        }

        // Upsert wards in chunks
        $wardRows = [];
        foreach ($wards as $code => $data) {
            $wardRows[] = [
                'ward_code'              => $code,
                'ward_name'              => $data['name'],
                'kenya_constituency_id'  => $consIdMap[$data['cons_code']],
                'kenya_county_id'        => $countyIdMap[$data['county_code']],
                'created_at'             => now(),
                'updated_at'             => now(),
            ];
        }

        foreach (array_chunk($wardRows, 200) as $chunk) {
            KenyaWard::upsert($chunk, ['ward_code'], ['ward_name', 'kenya_constituency_id', 'kenya_county_id', 'updated_at']);
        }

        $this->command->info("Counties: " . count($counties));
        $this->command->info("Constituencies: " . count($constituencies));
        $this->command->info("Wards: " . count($wards));
    }
}
