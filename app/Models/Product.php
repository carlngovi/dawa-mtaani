<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'ulid', 'sku_code', 'generic_name', 'brand_name',
        'therapeutic_category', 'unit_size', 'description',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function priceLists()
    {
        return $this->hasMany(WholesalePriceList::class);
    }

    public function activePriceLists()
    {
        return $this->hasMany(WholesalePriceList::class)
            ->where('is_active', true)
            ->where('stock_status', '!=', 'OUT_OF_STOCK');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('therapeutic_category', $category);
    }
}
