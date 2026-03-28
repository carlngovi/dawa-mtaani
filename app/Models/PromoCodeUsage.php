<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    protected $fillable = [
        'promo_code_id', 'customer_phone', 'customer_order_id', 'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }
}
