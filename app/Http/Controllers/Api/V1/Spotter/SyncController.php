<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Enums\SpotterSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SpotterSubmissionRequest;
use App\Services\SpotterDuplicateDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'array',
        ]);

        $spotter = $request->spotter_token->spotter;
        $results = [];
        $synced = 0;
        $failed = 0;
        $conflicts = 0;

        $submissionController = new SubmissionController;

        foreach ($request->items as $item) {
            try {
                // Check exact duplicate
                $exact = app(SpotterDuplicateDetector::class)
                    ->checkExact($item['pharmacy'] ?? '', $item['county'] ?? '');

                if ($exact && $exact->local_id !== ($item['id'] ?? '')) {
                    $results[] = [
                        'local_id' => $item['id'] ?? 'unknown',
                        'status' => 'conflict',
                        'server_id' => null,
                        'message' => 'Confirmed duplicate: ' . $exact->pharmacy,
                        'received_at' => now()->toISOString(),
                    ];
                    $conflicts++;

                    continue;
                }

                // Validate
                $validator = Validator::make($item, (new SpotterSubmissionRequest)->rules());
                if ($validator->fails()) {
                    $results[] = [
                        'local_id' => $item['id'] ?? 'unknown',
                        'status' => 'error',
                        'server_id' => null,
                        'message' => $validator->errors()->first(),
                        'received_at' => now()->toISOString(),
                    ];
                    $failed++;

                    continue;
                }

                $submission = $submissionController->processPayload($item, $spotter);
                $receipt = $submission->toSyncReceipt();
                $receipt['status'] = $submission->status === SpotterSubmissionStatus::Held ? 'conflict' : 'accepted';
                $results[] = $receipt;

                $submission->status === SpotterSubmissionStatus::Held ? $conflicts++ : $synced++;
            } catch (\Exception $e) {
                $results[] = [
                    'local_id' => $item['id'] ?? 'unknown',
                    'status' => 'error',
                    'server_id' => null,
                    'message' => $e->getMessage(),
                    'received_at' => now()->toISOString(),
                ];
                $failed++;
            }
        }

        return response()->json(compact('results', 'synced', 'failed', 'conflicts'));
    }
}
