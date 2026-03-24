<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    protected $fillable = [
        'order_id', 'wholesale_facility_id', 'product_id',
        'price_list_id', 'quantity', 'unit_price',
        'premium_applied', 'premium_amount', 'line_total',
        'payment_type', 'tranche_id', 'tier_id',
        'placer_user_id', 'delivery_facility_id',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'premium_amount'   => 'decimal:2',
        'line_total'       => 'decimal:2',
        'premium_applied'  => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function wholesaleFacility()
    {
        return $this->belongsTo(Facility::class, 'wholesale_facility_id');
    }

    public function priceList()
    {
        return $this->belongsTo(WholesalePriceList::class, 'price_list_id');
    }

    public function deliveryFacility()
    {
        return $this->belongsTo(Facility::class, 'delivery_facility_id');
    }
}
