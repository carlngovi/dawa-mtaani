<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader;

/**
 * ProductsSeeder
 *
 * Seeds the 49-SKU pilot product catalogue from CSV.
 * File: database/seeders/data/expanded_50_skus_seed.csv
 *
 * Pilot: Kilifi (Coast) + Migori (Lake Victoria) + Kisii (Western Highlands)
 * Uses upsert on sku_code — safe to re-run.
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/expanded_50_skus_seed.csv');

        if (! file_exists($file)) {
            $this->command->error('Missing: database/seeders/data/expanded_50_skus_seed.csv');
            $this->command->warn('Download the CSV and copy it into database/seeders/data/');
            return;
        }

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $rows    = [];
        $skipped = 0;
        $now     = now();

        foreach ($csv->getRecords() as $record) {
            $skuCode  = trim($record['sku_code']  ?? '');
            $name     = trim($record['generic_name'] ?? '');
            $category = trim($record['therapeutic_category'] ?? '');
            $unitSize = trim($record['unit_size'] ?? '');

            if (! $skuCode || ! $name) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'ulid'                  => (string) Str::ulid(),
                'sku_code'              => $skuCode,
                'generic_name'          => $name,
                'brand_name'            => null,
                'therapeutic_category'  => $category,
                'unit_size'             => $unitSize,
                'description'           => trim($record['description'] ?? ''),
                'is_active'             => true,
                'created_by'            => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        DB::table('products')->upsert(
            $rows,
            ['sku_code'],
            ['generic_name', 'therapeutic_category', 'unit_size', 'description', 'updated_at']
        );

        $this->command->info('Products seeded: ' . count($rows) . ' SKUs' .
            ($skipped ? " ({$skipped} skipped)" : ''));
    }
}
