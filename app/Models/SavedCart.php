<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedCart extends Model
{
    protected $fillable = [
        'ulid', 'name', 'owner_facility_id', 'owner_group_id',
        'is_group_cart', 'conflict_source_order_id',
        'conflict_resolution_status', 'created_by',
    ];

    protected $casts = [
        'is_group_cart' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(SavedCartLine::class);
    }

    public function ownerFacility()
    {
        return $this->belongsTo(Facility::class, 'owner_facility_id');
    }

    public function ownerGroup()
    {
        return $this->belongsTo(PharmacyGroup::class, 'owner_group_id');
    }

    public function isConflictDraft(): bool
    {
        return ! is_null($this->conflict_source_order_id);
    }
}
