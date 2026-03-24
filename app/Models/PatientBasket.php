<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientBasket extends Model
{
    protected $fillable = [
        'patient_phone', 'facility_id', 'session_token', 'reserved_until',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(PatientBasketLine::class, 'basket_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
