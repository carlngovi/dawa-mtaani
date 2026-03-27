<?php

namespace App\Models;

use App\Enums\SpotterFollowUpStatus;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotterFollowUp extends Model
{
    use HasUlids, SoftDeletes, Auditable;

    protected $table = 'spotter_follow_ups';

    protected $fillable = [
        'spotter_submission_id',
        'spotter_user_id',
        'next_step',
        'follow_up_date',
        'rep_notes',
        'status',
        'outcome_note',
        'completed_at',
        'overdue_alerted_at',
    ];

    protected $casts = [
        'status' => SpotterFollowUpStatus::class,
        'completed_at' => 'datetime',
        'overdue_alerted_at' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function submission()
    {
        return $this->belongsTo(SpotterSubmission::class, 'spotter_submission_id');
    }

    public function spotter()
    {
        return $this->belongsTo(User::class, 'spotter_user_id');
    }
}
