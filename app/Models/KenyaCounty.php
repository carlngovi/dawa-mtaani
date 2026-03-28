<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KenyaCounty extends Model
{
    protected $fillable = ['county_code', 'county_name'];

    public function constituencies(): HasMany
    {
        return $this->hasMany(KenyaConstituency::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(KenyaWard::class);
    }
}
