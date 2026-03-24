<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyGroupMember extends Model
{
    protected $fillable = [
        'group_id', 'facility_id', 'added_by', 'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(PharmacyGroup::class, 'group_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
