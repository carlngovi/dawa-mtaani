<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Http\Controllers\Api\V1\Spotter;

use App\Http\Controllers\Controller;
use App\Models\SpotterAttendance;
use App\Models\SpotterDuplicateReview;
use App\Models\SpotterFollowUp;
use App\Models\SpotterProfile;
use App\Models\SpotterSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    private function getScopeFromToken(Request $request): array
    {
        $token = $request->spotter_token;
        $user = $token->spotter;
        $role = $user->roles->first()?->name;
        $isSR = $role === 'sales_rep';
        $isCC = $role === 'county_coordinator';
        $isAdmin = in_array($role, ['admin', 'super_admin', 'technical_admin']);

        $spotterIds = $isSR
            ? SpotterProfile::where('sales_rep_user_id', $user->id)->pluck('user_id')
            : collect();

        $county = ($isCC || $isSR)
            ? SpotterProfile::where('user_id', $user->id)->value('county')
            : null;

        return compact('user', 'role', 'isSR', 'isCC', 'isAdmin', 'spotterIds', 'county');
    }

    public function stats(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $q = SpotterSubmission::query();

        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $q->where('county', $s['county']);
        }

        $submissionsToday = (clone $q)->whereDate('submitted_at', today())->count();
        $totalSubmissions = (clone $q)->whereNotIn('status', ['draft'])->count();
        $accepted = (clone $q)->where('status', 'accepted')->count();

        $dupQ = SpotterDuplicateReview::where('decision', 'pending');
        if ($s['isSR']) {
            $dupQ->where('tier', 'sr')->whereHas('submission', fn ($q2) => $q2->whereIn('spotter_user_id', $s['spotterIds']));
        } elseif ($s['isCC']) {
            $dupQ->where('tier', 'cc')->whereHas('submission', fn ($q2) => $q2->where('county', $s['county']));
        }
        $pendingDuplicates = $dupQ->count();

        $fuQ = SpotterFollowUp::where('status', 'overdue');
        if ($s['isSR']) {
            $fuQ->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $fuQ->whereHas('submission', fn ($q2) => $q2->where('county', $s['county']));
        }
        $overdueFollowUps = $fuQ->count();

        $spotterCount = $s['isSR'] ? $s['spotterIds']->count() : null;

        return response()->json(compact(
            'submissionsToday', 'totalSubmissions', 'accepted',
            'pendingDuplicates', 'overdueFollowUps', 'spotterCount'
        ));
    }

    public function submissions(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $q = SpotterSubmission::with('spotter:id,name');

        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $q->where('county', $s['county']);
        }

        if ($request->status) {
            $q->where('status', $request->status);
        }
        if ($request->period === 'today') {
            $q->whereDate('submitted_at', today());
        }

        return response()->json($q->orderByDesc('submitted_at')->paginate(20));
    }

    public function duplicates(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $tier = $s['isSR'] ? 'sr' : ($s['isCC'] ? 'cc' : 'admin');

        $reviews = SpotterDuplicateReview::with([
            'submission:id,pharmacy,ward,county,lat,lng,spotter_user_id,local_id',
            'submission.spotter:id,name',
            'matchedSubmission:id,pharmacy,ward,county,lat,lng',
        ])
            ->where('tier', $tier)
            ->where('decision', 'pending')
            ->oldest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function decide(Request $request, SpotterDuplicateReview $review): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $expectedTier = $s['isSR'] ? 'sr' : ($s['isCC'] ? 'cc' : 'admin');

        if ($review->tier->value !== $expectedTier) {
            return response()->json(['error' => 'Not authorised for this tier'], 403);
        }

        $request->validate([
            'decision' => 'required|in:confirmed_duplicate,not_duplicate',
            'notes' => 'nullable|string',
        ]);

        $review->update([
            'decision' => $request->decision,
            'reviewer_user_id' => $s['user']->id,
            'reviewed_at' => now(),
            'notes' => $request->notes,
        ]);

        if ($request->decision === 'confirmed_duplicate') {
            $review->submission->update(['status' => 'rejected']);
        } else {
            $nextTier = ['sr' => 'cc', 'cc' => 'admin'][$expectedTier] ?? null;
            $nextStatus = ['sr' => 'sr_reviewed', 'cc' => 'cc_verified', 'admin' => 'accepted'][$expectedTier];
            $review->submission->update(['status' => $nextStatus]);
            if ($nextTier) {
                SpotterDuplicateReview::create([
                    'spotter_submission_id' => $review->spotter_submission_id,
                    'matched_submission_id' => $review->matched_submission_id,
                    'tier' => $nextTier,
                    'decision' => 'pending',
                    'gps_distance_metres' => $review->gps_distance_metres,
                    'name_edit_distance' => $review->name_edit_distance,
                    'match_name' => $review->match_name,
                ]);
            }
        }

        return response()->json(['status' => 'decided', 'decision' => $request->decision]);
    }

    public function followups(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $q = SpotterFollowUp::with('submission:id,pharmacy,ward,county');
        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $q->whereHas('submission', fn ($q2) => $q2->where('county', $s['county']));
        }
        if ($request->status) {
            $q->where('status', $request->status);
        }

        return response()->json($q->orderByDesc('follow_up_date')->paginate(20));
    }

    public function attendance(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $q = SpotterAttendance::with('spotter:id,name');
        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $ids = SpotterProfile::where('county', $s['county'])->pluck('user_id');
            $q->whereIn('spotter_user_id', $ids);
        }
        if ($request->date) {
            $q->whereDate('date', $request->date);
        }

        return response()->json($q->orderByDesc('date')->paginate(25));
    }

    public function leaderboard(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $period = $request->query('period', 'programme');
        $dateFilter = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };

        $q = SpotterSubmission::whereNotIn('status', ['draft'])
            ->selectRaw("spotter_user_id, COUNT(*) as submissions, SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as activations")
            ->groupBy('spotter_user_id');

        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $q->where('county', $s['county']);
        }
        if ($dateFilter) {
            $q->where('submitted_at', '>=', $dateFilter);
        }

        $rows = $q->orderByDesc('activations')->orderByDesc('submissions')->get();
        $users = User::whereIn('id', $rows->pluck('spotter_user_id'))->pluck('name', 'id');
        $leaderboard = $rows->map(fn ($r, $i) => [
            'rank' => $i + 1,
            'name' => $users[$r->spotter_user_id] ?? 'Unknown',
            'submissions' => (int) $r->submissions,
            'activations' => (int) $r->activations,
        ])->values();

        return response()->json(compact('leaderboard', 'period'));
    }

    public function map(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        $q = SpotterSubmission::whereNotIn('status', ['draft', 'rejected'])
            ->select(['id', 'pharmacy', 'ward', 'county', 'potential', 'status', 'lat', 'lng', 'submitted_at']);
        if ($s['isSR']) {
            $q->whereIn('spotter_user_id', $s['spotterIds']);
        } elseif ($s['isCC']) {
            $q->where('county', $s['county']);
        }

        return response()->json(['pins' => $q->get()]);
    }

    public function spotters(Request $request): JsonResponse
    {
        $s = $this->getScopeFromToken($request);
        if (! $s['isSR']) {
            return response()->json(['spotters' => []]);
        }

        $spotters = User::whereIn('id', $s['spotterIds'])
            ->select(['id', 'name', 'email'])
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'today_submissions' => SpotterSubmission::where('spotter_user_id', $u->id)->whereDate('submitted_at', today())->count(),
                'total_submissions' => SpotterSubmission::where('spotter_user_id', $u->id)->whereNotIn('status', ['draft'])->count(),
                'clocked_in' => SpotterAttendance::where('spotter_user_id', $u->id)->whereDate('date', today())->whereNull('clock_out_at')->exists(),
            ]);

        return response()->json(['spotters' => $spotters]);
    }
}
