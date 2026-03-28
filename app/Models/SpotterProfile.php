<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotterProfile extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'county',
        'ward',
        'sales_rep_user_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_user_id');
    }

    public function getSalesRepName(): ?string
    {
        return $this->salesRep?->name;
    }
}
