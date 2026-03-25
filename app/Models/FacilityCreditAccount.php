<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityCreditAccount extends Model
{
    protected $fillable = [
        'facility_id', 'tranche_id', 'account_status',
        'approved_at', 'suspended_at', 'suspension_reason',
        'next_assessment_due',
    ];

    protected $casts = [
        'approved_at'          => 'datetime',
        'suspended_at'         => 'datetime',
        'next_assessment_due'  => 'date',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function tranche()
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }

    public function trancheBalances()
    {
        return $this->hasMany(FacilityTrancheBalance::class, 'credit_account_id');
    }

    public function events()
    {
        return $this->hasMany(CreditEvent::class, 'credit_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('account_status', 'ACTIVE');
    }

    public function scopeSuspended($query)
    {
        return $query->where('account_status', 'SUSPENDED');
    }

    public function scopePendingAssessment($query)
    {
        return $query->where('account_status', 'PENDING_ASSESSMENT');
    }
}
