<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Models;

use App\Enums\SpotterPotential;
use App\Enums\SpotterSubmissionStatus;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotterSubmission extends Model
{
    use HasUlids, SoftDeletes, Auditable;

    protected $table = 'spotter_submissions';

    public const CONFIDENTIAL = ['foot_traffic', 'stock_level', 'notes'];

    protected $fillable = [
        'local_id',
        'spotter_user_id',
        'status',
        'county',
        'ward',
        'town',
        'address',
        'lat',
        'lng',
        'gps_accuracy',
        'pharmacy',
        'open_time',
        'close_time',
        'days_per_week',
        'visit_date',
        'owner_name',
        'owner_phone',
        'pharmacy_phone',
        'owner_email',
        'owner_present',
        'foot_traffic',
        'stock_level',
        'notes',
        'potential',
        'follow_up',
        'callback_time',
        'next_step',
        'follow_up_date',
        'rep_notes',
        'brochure',
        'photo_path',
        'photo_name',
        'photo_size_bytes',
        'submitted_at',
        'received_at',
    ];

    protected $casts = [
        'status' => SpotterSubmissionStatus::class,
        'potential' => SpotterPotential::class,
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'owner_present' => 'boolean',
        'follow_up' => 'boolean',
        'brochure' => 'boolean',
        'submitted_at' => 'datetime',
        'received_at' => 'datetime',
        'visit_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public function isConfidential(string $field): bool
    {
        return in_array($field, self::CONFIDENTIAL);
    }

    public function spotter()
    {
        return $this->belongsTo(User::class, 'spotter_user_id');
    }

    public function followUp()
    {
        return $this->hasOne(SpotterFollowUp::class);
    }

    public function duplicateReviews()
    {
        return $this->hasMany(SpotterDuplicateReview::class);
    }

    public function scopePublicFields($query)
    {
        $all = (new static)->getFillable();

        return $query->select(array_merge(
            ['id'],
            array_diff($all, self::CONFIDENTIAL)
        ));
    }

    public function toSyncReceipt(): array
    {
        return [
            'local_id' => $this->local_id,
            'status' => $this->status === SpotterSubmissionStatus::Held ? 'conflict' : 'accepted',
            'server_id' => $this->id,
            'message' => $this->status->label(),
            'received_at' => $this->received_at?->toISOString() ?? now()->toISOString(),
        ];
    }
}
