<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstruction extends Model
{
    protected $fillable = [
        'ulid', 'order_id', 'tranche_id', 'party_id',
        'instruction_amount', 'party_risk_percentage',
        'idempotency_key', 'status', 'sent_at', 'acknowledged_at',
        'processed_at', 'failed_at', 'failure_reason',
        'retry_count', 'party_reference',
    ];

    protected $casts = [
        'sent_at'        => 'datetime',
        'acknowledged_at'=> 'datetime',
        'processed_at'   => 'datetime',
        'failed_at'      => 'datetime',
        'instruction_amount'      => 'decimal:2',
        'party_risk_percentage'   => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
