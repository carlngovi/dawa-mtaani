<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientOrder extends Model
{
    protected $fillable = [
        'ulid', 'user_id', 'patient_phone', 'patient_name', 'facility_id', 'status',
        'subtotal_amount', 'discount_amount', 'total_amount',
        'platform_fee_pct', 'platform_fee_amount', 'facility_net_amount',
        'promo_code_id', 'collection_window_start', 'collection_window_end',
        'mpesa_checkout_request_id', 'mpesa_receipt_number',
        'paid_at', 'collected_at', 'rejection_reason',
    ];

    protected $casts = [
        'subtotal_amount'         => 'decimal:2',
        'discount_amount'         => 'decimal:2',
        'total_amount'            => 'decimal:2',
        'platform_fee_pct'        => 'decimal:2',
        'platform_fee_amount'     => 'decimal:2',
        'facility_net_amount'     => 'decimal:2',
        'paid_at'                 => 'datetime',
        'collected_at'            => 'datetime',
        'collection_window_start' => 'datetime',
        'collection_window_end'   => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(PatientOrderLine::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
