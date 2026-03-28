<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KenyaConstituency extends Model
{
    protected $fillable = ['constituency_code', 'constituency_name', 'kenya_county_id'];

    public function county(): BelongsTo
    {
        return $this->belongsTo(KenyaCounty::class, 'kenya_county_id');
    }

    public function wards(): HasMany
    {
        return $this->hasMany(KenyaWard::class);
    }
}
