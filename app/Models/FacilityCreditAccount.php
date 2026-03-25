<?php
namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityCreditAccount extends Model
{
    use Auditable;

    protected $fillable = [
        'facility_id', 'account_status', 'suspended_at', 'suspended_reason',
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(FacilityTrancheBalance::class, 'facility_id', 'facility_id');
    }

    public function isActive(): bool
    {
        return $this->account_status === 'ACTIVE';
    }

    public function isSuspended(): bool
    {
        return $this->account_status === 'SUSPENDED';
    }
}
