<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientBasketLine extends Model
{
    protected $fillable = [
        'basket_id', 'product_id', 'quantity', 'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function basket()
    {
        return $this->belongsTo(PatientBasket::class, 'basket_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
