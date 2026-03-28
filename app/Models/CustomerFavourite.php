<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFavourite extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_phone', 'product_id', 'facility_id', 'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
