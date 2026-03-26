<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDistribution extends Model
{
    protected $fillable = [
        'tranche_id', 'party_id', 'source_repayment_id',
        'party_return_percentage', 'distributed_amount', 'distributed_at',
    ];

    protected $casts = [
        'distributed_at'          => 'datetime',
        'party_return_percentage' => 'decimal:2',
        'distributed_amount'      => 'decimal:2',
    ];

    public function repayment(): BelongsTo
    {
        return $this->belongsTo(RepaymentRecord::class, 'source_repayment_id');
    }
}
