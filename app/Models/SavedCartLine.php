<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedCartLine extends Model
{
    protected $fillable = [
        'saved_cart_id', 'product_id',
        'wholesale_facility_id', 'quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(SavedCart::class, 'saved_cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function wholesaleFacility()
    {
        return $this->belongsTo(Facility::class, 'wholesale_facility_id');
    }
}
