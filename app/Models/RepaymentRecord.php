<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentRecord extends Model
{
    protected $fillable = [
        'facility_id', 'order_id', 'tranche_id',
        'amount_due', 'amount_paid', 'due_at', 'paid_at',
        'payment_method', 'mpesa_reference', 'days_to_repay',
        'progression_applied', 'status',
    ];

    protected $casts = [
        'due_at'               => 'date',
        'paid_at'              => 'datetime',
        'amount_due'           => 'decimal:2',
        'amount_paid'          => 'decimal:2',
        'progression_applied'  => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
