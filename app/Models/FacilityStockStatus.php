<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityStockStatus extends Model
{
    protected $table = 'facility_stock_status';

    protected $fillable = [
        'wholesale_facility_id',
        'product_id',
        'stock_status',
        'stock_quantity',
        'updated_by',
    ];

    public function wholesaleFacility()
    {
        return $this->belongsTo(Facility::class, 'wholesale_facility_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_status === 'OUT_OF_STOCK';
    }

    public function isInStock(): bool
    {
        return $this->stock_status === 'IN_STOCK';
    }
}
