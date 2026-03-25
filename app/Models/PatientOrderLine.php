<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientOrderLine extends Model
{
    protected $fillable = [
        'patient_order_id', 'product_id', 'quantity',
        'unit_price', 'line_discount', 'line_total',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'line_discount'  => 'decimal:2',
        'line_total'     => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(PatientOrder::class, 'patient_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
