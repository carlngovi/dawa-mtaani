<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditEvent extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'ulid', 'facility_id', 'tranche_id', 'tier_id', 'order_id',
        'event_type', 'amount', 'balance_before', 'balance_after', 'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'notes'          => 'array',
        'created_at'     => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function tranche(): BelongsTo
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(CreditTier::class, 'tier_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
