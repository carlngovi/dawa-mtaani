<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PharmacyGroup extends Model
{
    use Auditable;

    protected $fillable = [
        'ulid', 'group_name', 'group_owner_name', 'group_owner_phone',
        'group_owner_email', 'group_owner_user_id', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function members()
    {
        return $this->hasMany(PharmacyGroupMember::class, 'group_id');
    }

    public function facilities()
    {
        return $this->hasManyThrough(
            Facility::class,
            PharmacyGroupMember::class,
            'group_id',
            'id',
            'id',
            'facility_id'
        );
    }

    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'group_owner_user_id');
    }
}
