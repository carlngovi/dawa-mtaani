<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class FacilityPricingAgreement extends Model
{
    use Auditable;

    protected $fillable = [
        'facility_id', 'premium_type', 'premium_value',
        'effective_from', 'expires_at', 'agreed_by',
    ];

    protected $casts = [
        'premium_value'  => 'decimal:4',
        'effective_from' => 'date',
        'expires_at'     => 'date',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function isActive(): bool
    {
        if (is_null($this->expires_at)) {
            return true;
        }
        return $this->expires_at->isFuture();
    }
}
