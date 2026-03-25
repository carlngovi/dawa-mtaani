<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_value',
        'buy_quantity', 'get_quantity', 'is_automatic', 'auto_trigger_condition',
        'valid_from', 'valid_until', 'usage_cap_total', 'usage_cap_per_patient',
        'stackable', 'created_by',
    ];

    protected $casts = [
        'discount_value'       => 'decimal:2',
        'min_order_value'      => 'decimal:2',
        'is_automatic'         => 'boolean',
        'auto_trigger_condition' => 'array',
        'valid_from'           => 'date',
        'valid_until'          => 'date',
        'stackable'            => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }
}
