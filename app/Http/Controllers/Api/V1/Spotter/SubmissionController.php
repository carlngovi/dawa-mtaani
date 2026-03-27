<?php

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpotterSubmissionRequest;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterSubmission;
use App\Models\User;
use App\Services\SpotterDuplicateDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function processPayload(array $data, User $spotter): SpotterSubmission
    {
        // Idempotency check
        $existing = SpotterSubmission::where('local_id', $data['id'])->first();
        if ($existing) {
            return $existing;
        }

        $detector = app(SpotterDuplicateDetector::class);
        $dupes = $detector->run(
            $data['pharmacy'],
            $data['ward'],
            $data['county'],
            (float) $data['lat'],
            (float) $data['lng'],
        );

        // Handle photo
        $photoPath = null;
        $photoSize = null;
        if (! empty($data['photoData'])) {
            $decoded = base64_decode($data['photoData']);
            $filename = now()->format('Y/m') . '/' . $data['id'] . '.jpg';
            Storage::disk('local')->put('spotter-photos/' . $filename, $decoded);
            $photoPath = 'spotter-photos/' . $filename;
            $photoSize = strlen($decoded);
        }

        // Map payload to DB columns
        $attrs = [
            'local_id' => $data['id'],
            'spotter_user_id' => $spotter->id,
            'county' => $data['county'],
            'ward' => $data['ward'],
            'town' => $data['town'],
            'address' => $data['address'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'gps_accuracy' => $data['gpsAccuracy'] ?? null,
            'pharmacy' => $data['pharmacy'],
            'open_time' => $data['openTime'],
            'close_time' => $data['closeTime'],
            'days_per_week' => (int) $data['daysPerWeek'],
            'visit_date' => $data['date'],
            'owner_name' => $data['ownerName'],
            'owner_phone' => $data['ownerPhone'],
            'pharmacy_phone' => $data['pharmacyPhone'] ?? null,
            'owner_email' => $data['ownerEmail'] ?? null,
            'owner_present' => $data['ownerPresent'] === 'Yes',
            'foot_traffic' => $data['footTraffic'],
            'stock_level' => $data['stockLevel'],
            'notes' => $data['notes'] ?? null,
            'potential' => $data['potential'],
            'follow_up' => $data['followUp'] === 'Yes',
            'callback_time' => $data['callbackTime'] ?? null,
            'next_step' => $data['nextStep'],
            'follow_up_date' => $data['followUpDate'] ?? null,
            'rep_notes' => $data['repNotes'] ?? null,
            'brochure' => $data['brochure'] === 'Yes',
            'photo_path' => $photoPath,
            'photo_name' => $data['photoName'] ?? null,
            'photo_size_bytes' => $photoSize,
            'submitted_at' => now(),
            'received_at' => now(),
        ];

        if ($dupes['fuzzy'] || $dupes['gps']) {
            $attrs['status'] = 'held';
            $submission = SpotterSubmission::create($attrs);

            $matched = $dupes['fuzzy'] ?? $dupes['gps'];
            $distance = $dupes['gps']
                ? $detector->haversineMetres((float) $data['lat'], (float) $data['lng'], (float) $matched->lat, (float) $matched->lng)
                : null;

            SpotterDuplicateReview::create([
                'spotter_submission_id' => $submission->id,
                'matched_submission_id' => $matched->id,
                'tier' => 'sr',
                'decision' => 'pending',
                'gps_distance_metres' => $distance,
                'name_edit_distance' => $dupes['fuzzy']
                    ? $detector->levenshteinDistance($data['pharmacy'], $matched->pharmacy)
                    : null,
                'match_name' => $matched->pharmacy,
            ]);
        } else {
            $attrs['status'] = 'submitted';
            $submission = SpotterSubmission::create($attrs);

            if ($data['nextStep'] !== 'no_action') {
                SpotterFollowUp::create([
                    'spotter_submission_id' => $submission->id,
                    'spotter_user_id' => $spotter->id,
                    'next_step' => $data['nextStep'],
                    'follow_up_date' => $data['followUpDate'],
                    'rep_notes' => $data['repNotes'] ?? null,
                ]);
            }
        }

        return $submission;
    }

    public function store(SpotterSubmissionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $spotter = $request->spotter_token->spotter;

        // Check exact duplicate first — return 409 if found
        $exact = app(SpotterDuplicateDetector::class)->checkExact($data['pharmacy'], $data['county']);
        if ($exact) {
            return response()->json([
                'error' => 'This pharmacy has already been submitted.',
                'match' => $exact->pharmacy,
                'distance' => 0,
            ], 409);
        }

        $submission = $this->processPayload($data, $spotter);

        return response()->json([
            'id' => $submission->id,
            'local_id' => $submission->local_id,
            'status' => $submission->status->value,
            'received_at' => $submission->received_at->toISOString(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $spotter = $request->spotter_token->spotter;

        $submissions = SpotterSubmission::where('spotter_user_id', $spotter->id)
            ->select(array_merge(
                ['id'],
                array_diff(
                    (new SpotterSubmission)->getFillable(),
                    SpotterSubmission::CONFIDENTIAL,
                ),
            ))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'submissions' => $submissions,
            'total' => $submissions->count(),
        ]);
    }
}
