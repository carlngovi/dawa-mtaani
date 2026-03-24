<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpbRegistryCache extends Model
{
    protected $table = 'ppb_registry_cache';

    protected $fillable = [
        'licence_number', 'facility_name', 'ppb_type', 'licence_status',
        'registered_address', 'licence_expiry_date', 'last_uploaded_at',
        'upload_batch_id',
    ];

    protected $casts = [
        'licence_expiry_date' => 'date',
        'last_uploaded_at'    => 'datetime',
    ];
}
