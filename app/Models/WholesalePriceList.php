<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class WholesalePriceList extends Model
{
    use Auditable;

    protected $fillable = [
        'wholesale_facility_id', 'product_id', 'unit_price',
        'effective_from', 'expires_at', 'stock_status',
        'stock_quantity', 'is_active',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'effective_from' => 'date',
        'expires_at'     => 'date',
        'is_active'      => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function wholesaleFacility()
    {
        return $this->belongsTo(Facility::class, 'wholesale_facility_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'IN_STOCK');
    }

    public function isInStock(): bool
    {
        return $this->stock_status === 'IN_STOCK';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
