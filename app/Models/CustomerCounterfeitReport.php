<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CustomerCounterfeitReport extends Model
{
    protected $fillable = [
        'facility_id', 'product_id', 'customer_phone',
        'report_notes', 'status', 'notified_ppb_at',
    ];

    protected $casts = [
        'notified_ppb_at' => 'datetime',
    ];

    public function setCustomerPhoneAttribute(string $value): void
    {
        $this->attributes['customer_phone'] = Hash::make($value);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
