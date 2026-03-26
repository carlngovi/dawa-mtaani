<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopayPaymentAttempt extends Model
{
    protected $fillable = [
        'order_id', 'attempt_number', 'mpesa_checkout_request_id',
        'mpesa_result_code', 'failure_reason', 'status',
        'initiated_at', 'completed_at',
    ];

    protected $casts = [
        'initiated_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
