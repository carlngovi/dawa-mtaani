<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityTrancheBalance extends Model
{
    protected $fillable = [
        'credit_account_id', 'tier_id',
        'allocated_amount', 'drawn_amount', 'available_amount',
        'last_drawn_at', 'last_repaid_at',
    ];

    protected $casts = [
        'allocated_amount'  => 'decimal:2',
        'drawn_amount'      => 'decimal:2',
        'available_amount'  => 'decimal:2',
        'last_drawn_at'     => 'datetime',
        'last_repaid_at'    => 'datetime',
    ];

    public function creditAccount()
    {
        return $this->belongsTo(FacilityCreditAccount::class, 'credit_account_id');
    }

    public function tier()
    {
        return $this->belongsTo(CreditTier::class, 'tier_id');
    }
}
