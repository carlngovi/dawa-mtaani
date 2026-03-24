<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PatientCounterfeitReport extends Model
{
    protected $fillable = [
        'facility_id', 'product_id', 'patient_phone',
        'report_notes', 'status', 'notified_ppb_at',
    ];

    protected $casts = [
        'notified_ppb_at' => 'datetime',
    ];

    public function setPatientPhoneAttribute(string $value): void
    {
        $this->attributes['patient_phone'] = Hash::make($value);
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
