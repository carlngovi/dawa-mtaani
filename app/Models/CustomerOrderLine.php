<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderLine extends Model
{
    protected $fillable = [
        'customer_order_id', 'product_id', 'quantity',
        'unit_price', 'line_discount', 'line_total',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'line_discount'  => 'decimal:2',
        'line_total'     => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
