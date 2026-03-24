<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'ulid', 'retail_facility_id', 'placed_by_user_id',
        'is_group_order', 'is_network_member', 'order_type',
        'source_channel', 'status', 'total_amount',
        'credit_amount', 'cash_amount', 'notes',
        'copay_status', 'copay_escalated_at',
        'copay_override_by', 'copay_override_reason',
        'copay_override_additional_attempts',
        'manual_payment_reference',
        'submitted_at', 'confirmed_at',
    ];

    protected $casts = [
        'is_group_order'    => 'boolean',
        'is_network_member' => 'boolean',
        'total_amount'      => 'decimal:2',
        'credit_amount'     => 'decimal:2',
        'cash_amount'       => 'decimal:2',
        'submitted_at'      => 'datetime',
        'confirmed_at'      => 'datetime',
        'copay_escalated_at' => 'datetime',
    ];

    public function retailFacility()
    {
        return $this->belongsTo(Facility::class, 'retail_facility_id');
    }

    public function placedBy()
    {
        return $this->belongsTo(User::class, 'placed_by_user_id');
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function deliverySplits()
    {
        return $this->hasMany(OrderDeliverySplit::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeForFacility($query, int $facilityId)
    {
        return $query->where('retail_facility_id', $facilityId);
    }

    public function isNetworkOrder(): bool
    {
        return $this->is_network_member;
    }

    public function requiresCopay(): bool
    {
        return $this->copay_status !== 'NOT_REQUIRED';
    }
}
