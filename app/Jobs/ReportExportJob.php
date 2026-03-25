<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportExportJob extends MonitoredJob
{
    public function __construct(
        private readonly int $exportId
    ) {}

    public function execute(): void
    {
        $export = DB::table('reporting_exports')
            ->where('id', $this->exportId)
            ->first();

        if (! $export) {
            Log::error('ReportExportJob: export not found', ['id' => $this->exportId]);
            return;
        }

        DB::table('reporting_exports')
            ->where('id', $this->exportId)
            ->update(['status' => 'GENERATING']);

        try {
            $parameters = json_decode($export->parameters, true) ?? [];
            $metricDefinitions = json_decode($export->metric_definitions, true) ?? [];

            $data = $this->gatherData($export->export_type, $parameters, $metricDefinitions);

            $fileName = 'reports/' . $export->export_type . '_' . now()->format('Ymd_His') . '.json';
            Storage::put($fileName, json_encode($data, JSON_PRETTY_PRINT));

            $expiresAt = Carbon::now('UTC')->addHours(24);

            DB::table('reporting_exports')
                ->where('id', $this->exportId)
                ->update([
                    'status'              => 'READY',
                    'file_path'           => $fileName,
                    'row_count'           => count($data['rows'] ?? []),
                    'download_url'        => $fileName,
                    'download_expires_at' => $expiresAt,
                    'updated_at'          => Carbon::now('UTC'),
                ]);

            Log::info('ReportExportJob: export ready', [
                'export_id' => $this->exportId,
                'file'      => $fileName,
                'rows'      => count($data['rows'] ?? []),
            ]);

        } catch (\Throwable $e) {
            DB::table('reporting_exports')
                ->where('id', $this->exportId)
                ->update([
                    'status'     => 'FAILED',
                    'updated_at' => Carbon::now('UTC'),
                ]);

            Log::error('ReportExportJob: failed', [
                'export_id' => $this->exportId,
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->completed();
    }

    private function gatherData(string $exportType, array $parameters, array $metricDefinitions): array
    {
        $dateFrom = $parameters['date_from'] ?? now()->subDays(30)->toDateString();
        $dateTo = $parameters['date_to'] ?? now()->toDateString();

        return match ($exportType) {
            'network_summary' => $this->exportNetworkSummary($dateFrom, $dateTo, $parameters),
            'facility_performance' => $this->exportFacilityPerformance($dateFrom, $dateTo, $parameters),
            'order_history' => $this->exportOrderHistory($dateFrom, $dateTo, $parameters),
            'credit_overview' => $this->exportCreditOverview($dateFrom, $dateTo, $parameters),
            default => ['rows' => [], 'export_type' => $exportType, 'error' => 'Unknown export type'],
        };
    }

    private function exportNetworkSummary(string $dateFrom, string $dateTo, array $parameters): array
    {
        $rows = DB::table('network_daily_summaries')
            ->whereBetween('summary_date', [$dateFrom, $dateTo])
            ->orderBy('summary_date', 'desc')
            ->get()
            ->toArray();

        return [
            'export_type' => 'network_summary',
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'rows'        => $rows,
            'generated_at' => now('UTC')->toISOString(),
        ];
    }

    private function exportFacilityPerformance(string $dateFrom, string $dateTo, array $parameters): array
    {
        $county = $parameters['county'] ?? null;

        $query = DB::table('facilities as f')
            ->leftJoin('orders as o', function ($join) use ($dateFrom, $dateTo) {
                $join->on('o.retail_facility_id', '=', 'f.id')
                     ->whereBetween('o.created_at', [$dateFrom, $dateTo])
                     ->whereNull('o.deleted_at');
            })
            ->whereNull('f.deleted_at')
            ->groupBy('f.id', 'f.facility_name', 'f.county', 'f.facility_status')
            ->select([
                'f.id',
                'f.facility_name',
                'f.county',
                'f.facility_status',
                DB::raw('COUNT(o.id) as order_count'),
                DB::raw('COALESCE(SUM(o.total_amount), 0) as total_gmv'),
            ]);

        if ($county) {
            $query->where('f.county', $county);
        }

        $rows = $query->get()->toArray();

        return [
            'export_type' => 'facility_performance',
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'rows'        => $rows,
            'generated_at' => now('UTC')->toISOString(),
        ];
    }

    private function exportOrderHistory(string $dateFrom, string $dateTo, array $parameters): array
    {
        $facilityId = $parameters['facility_id'] ?? null;

        $query = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->whereBetween('o.created_at', [$dateFrom, $dateTo])
            ->whereNull('o.deleted_at')
            ->select([
                'o.ulid',
                'o.status',
                'o.order_type',
                'o.total_amount',
                'o.credit_amount',
                'o.cash_amount',
                'o.source_channel',
                'o.created_at',
                'f.facility_name',
                'f.county',
            ])
            ->orderBy('o.created_at', 'desc');

        if ($facilityId) {
            $query->where('o.retail_facility_id', $facilityId);
        }

        $rows = $query->limit(10000)->get()->toArray();

        return [
            'export_type' => 'order_history',
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'rows'        => $rows,
            'generated_at' => now('UTC')->toISOString(),
        ];
    }

    private function exportCreditOverview(string $dateFrom, string $dateTo, array $parameters): array
    {
        // Placeholder — populated when credit module is built
        return [
            'export_type' => 'credit_overview',
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'rows'        => [],
            'note'        => 'Credit module not yet implemented.',
            'generated_at' => now('UTC')->toISOString(),
        ];
    }
}
