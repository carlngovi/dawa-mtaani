<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementRecord extends Model
{
    protected $fillable = [
        'facility_id', 'settlement_date', 'gross_amount', 'platform_fee',
        'net_amount', 'order_count', 'is_network_member',
        'mpesa_b2c_reference', 'settled_at',
    ];

    protected $casts = [
        'settlement_date'    => 'date',
        'gross_amount'       => 'decimal:2',
        'platform_fee'       => 'decimal:2',
        'net_amount'         => 'decimal:2',
        'is_network_member'  => 'boolean',
        'settled_at'         => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
