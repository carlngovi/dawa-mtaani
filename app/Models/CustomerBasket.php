<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBasket extends Model
{
    protected $fillable = [
        'customer_phone', 'facility_id', 'session_token', 'reserved_until',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(CustomerBasketLine::class, 'basket_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
