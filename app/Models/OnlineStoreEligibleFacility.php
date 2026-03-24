<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineStoreEligibleFacility extends Model
{
    protected $fillable = [
        'facility_id',
        'qualified_at',
        'pos_data_days',
        'variance_score',
        'branding_mode',
        'is_network_member',
        'is_active',
    ];

    protected $casts = [
        'qualified_at'      => 'datetime',
        'is_network_member' => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
