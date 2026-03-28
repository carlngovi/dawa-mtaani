<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SpotterAttendance extends Model
{
    use HasUlids, Auditable;

    protected $table = 'spotter_attendances';

    protected $fillable = [
        'spotter_user_id',
        'server_id',
        'date',
        'clock_in_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_out_at',
        'clock_out_lat',
        'clock_out_lng',
        'auto_closed',
        'split_shift_index',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'clock_in_lat' => 'decimal:7',
        'clock_in_lng' => 'decimal:7',
        'clock_out_lat' => 'decimal:7',
        'clock_out_lng' => 'decimal:7',
        'auto_closed' => 'boolean',
        'date' => 'date',
    ];

    public function spotter()
    {
        return $this->belongsTo(User::class, 'spotter_user_id');
    }

    public function totalHours(): ?float
    {
        if ($this->clock_out_at === null) {
            return null;
        }

        return round($this->clock_in_at->diffInMinutes($this->clock_out_at) / 60, 2);
    }
}
