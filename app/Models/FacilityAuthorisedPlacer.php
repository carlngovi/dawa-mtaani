<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class FacilityAuthorisedPlacer extends Model
{
    use Auditable;

    protected $fillable = [
        'facility_id', 'user_id', 'added_by', 'added_at', 'is_active',
    ];

    protected $casts = [
        'added_at'  => 'datetime',
        'is_active' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
