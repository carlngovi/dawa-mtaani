<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'credit_account_id', 'tier_id', 'event_type',
        'amount', 'running_balance', 'reference',
        'triggered_by', 'notes', 'occurred_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'running_balance' => 'decimal:2',
        'occurred_at'     => 'datetime',
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
