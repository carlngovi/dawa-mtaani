<?php

namespace App\Models;

use App\Enums\SpotterDuplicateDecision;
use App\Enums\SpotterDuplicateTier;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SpotterDuplicateReview extends Model
{
    use HasUlids, Auditable;

    protected $table = 'spotter_duplicate_reviews';

    protected $fillable = [
        'spotter_submission_id',
        'matched_submission_id',
        'tier',
        'reviewer_user_id',
        'decision',
        'gps_distance_metres',
        'name_edit_distance',
        'match_name',
        'notes',
        'reviewed_at',
    ];

    protected $casts = [
        'tier' => SpotterDuplicateTier::class,
        'decision' => SpotterDuplicateDecision::class,
        'reviewed_at' => 'datetime',
        'gps_distance_metres' => 'decimal:2',
    ];

    public function submission()
    {
        return $this->belongsTo(SpotterSubmission::class, 'spotter_submission_id');
    }

    public function matchedSubmission()
    {
        return $this->belongsTo(SpotterSubmission::class, 'matched_submission_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
