<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityGpsController extends Controller
{
    // PATCH /api/v1/facilities/{ulid}/gps
    public function update(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',
            'accuracy_meters' => 'nullable|integer|min:0',
            'capture_method'  => 'required|in:DEVICE_AUTO,MAP_PIN,MANUAL_ENTRY,ADMIN_UPLOAD',
        ]);

        // Accuracy check for DEVICE_AUTO
        $warnings = [];
        if (
            $validated['capture_method'] === 'DEVICE_AUTO' &&
            isset($validated['accuracy_meters']) &&
            $validated['accuracy_meters'] > 50
        ) {
            $warnings[] = 'GPS accuracy exceeds 50 meters. Consider moving outdoors or waiting for better signal.';
        }

        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        // Store old coordinates in audit log if updating existing GPS
        $auditBefore = null;
        if ($facility->latitude) {
            $auditBefore = [
                'latitude'  => $facility->latitude,
                'longitude' => $facility->longitude,
            ];
        }

        DB::table('facilities')
            ->where('ulid', $ulid)
            ->update([
                'latitude'           => $validated['latitude'],
                'longitude'          => $validated['longitude'],
                'gps_accuracy_meters' => $validated['accuracy_meters'] ?? null,
                'gps_captured_at'    => Carbon::now('UTC'),
                'gps_captured_by'    => $request->user()->id,
                'gps_capture_method' => $validated['capture_method'],
                'updated_by'         => $request->user()->id,
                'updated_at'         => Carbon::now('UTC'),
            ]);

        // Write audit trail
        DB::table('audit_logs')->insert([
            'facility_id'    => $facility->id,
            'user_id'        => $request->user()->id,
            'action'         => 'facility_gps_updated',
            'model_type'     => 'App\Models\Facility',
            'model_id'       => $facility->id,
            'payload_before' => $auditBefore ? json_encode($auditBefore) : null,
            'payload_after'  => json_encode([
                'latitude'       => $validated['latitude'],
                'longitude'      => $validated['longitude'],
                'capture_method' => $validated['capture_method'],
            ]),
            'ip_address'     => $request->ip() ?? '0.0.0.0',
            'user_agent'     => $request->userAgent(),
            'created_at'     => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message'  => 'GPS coordinates updated.',
            'warnings' => $warnings,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);
    }

    // GET /api/v1/admin/facilities/gps-pending
    public function gpsPending(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('facilities')
            ->whereNull('latitude')
            ->whereNull('deleted_at')
            ->select(['id', 'ulid', 'facility_name', 'county', 'sub_county', 'onboarding_status']);

        if ($request->filled('county')) {
            $query->where('county', $request->county);
        }

        $facilities = $query->paginate(50);

        return response()->json($facilities);
    }

    // POST /api/v1/admin/facilities/gps-bulk-upload
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $contents = file_get_contents($file->getRealPath());
        $lines = explode("\n", trim($contents));

        $updated = 0;
        $notMatched = 0;
        $errors = [];

        // Skip header row
        $startRow = 1;

        for ($i = $startRow; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $lineNumber = $i + 1;

            try {
                $columns = str_getcsv($line);

                $licenceNumber = trim($columns[0] ?? '');
                $latitude      = trim($columns[1] ?? '');
                $longitude     = trim($columns[2] ?? '');

                if (empty($licenceNumber)) {
                    throw new \InvalidArgumentException('licence_number is required');
                }

                if (! is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
                    throw new \InvalidArgumentException("Invalid latitude: {$latitude}");
                }

                if (! is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
                    throw new \InvalidArgumentException("Invalid longitude: {$longitude}");
                }

                $facility = DB::table('facilities')
                    ->where('ppb_licence_number', $licenceNumber)
                    ->whereNull('deleted_at')
                    ->first();

                if (! $facility) {
                    $notMatched++;
                    $errors[] = [
                        'line'    => $lineNumber,
                        'licence' => $licenceNumber,
                        'error'   => 'Licence number not found',
                    ];
                    continue;
                }

                DB::table('facilities')
                    ->where('id', $facility->id)
                    ->update([
                        'latitude'           => (float) $latitude,
                        'longitude'          => (float) $longitude,
                        'gps_captured_at'    => Carbon::now('UTC'),
                        'gps_captured_by'    => $request->user()->id,
                        'gps_capture_method' => 'ADMIN_UPLOAD',
                        'updated_by'         => $request->user()->id,
                        'updated_at'         => Carbon::now('UTC'),
                    ]);

                $updated++;

            } catch (\Throwable $e) {
                $errors[] = [
                    'line'  => $lineNumber,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message'     => 'Bulk GPS upload complete.',
            'rows_updated'     => $updated,
            'rows_not_matched' => $notMatched,
            'errors'           => $errors,
        ]);
    }
}
